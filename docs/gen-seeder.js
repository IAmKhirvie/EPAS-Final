const fs = require('fs');
const src = fs.readFileSync(__dirname + '/generate-docx.js', 'utf8');

const sheets = ['sheet11','sheet12','sheet13','sheet14','sheet15','sheet16'];
const sheetNumbers = ['1.1','1.2','1.3','1.4','1.5','1.6'];
const titles = [
  'Introduction to Basic Electronics and Electricity',
  "Resistors, Color Coding, Conversion, Tolerance, Circuits and Ohm's Law",
  'Capacitors and Diodes',
  "Transistors, Integrated Circuits (ICs) and Transformers",
  'Schematic Diagram, Pictorial Diagram, Block Diagram and PCB Making',
  'Soldering and De-soldering, Terminaling and Connecting, Troubleshooting Process'
];

function extractTopics(block) {
  // Find all function calls with position, type, and text
  const patterns = [
    { re: /heading\("([^"]+?)",\s*HeadingLevel\.HEADING_2\)/g, type: 'h2', fmt: (m) => m[1] },
    { re: /heading\("([^"]+?)",\s*HeadingLevel\.HEADING_3\)/g, type: 'h3', fmt: (m) => m[1] },
    { re: /bold\("([^"]+?)"\)/g, type: 'bold', fmt: (m) => '<strong>' + m[1] + '</strong>' },
    { re: /para\("([^"]+?)"\)/g, type: 'para', fmt: (m) => '<p>' + m[1] + '</p>' },
    { re: /italicPara\("([^"]+?)"\)/g, type: 'para', fmt: (m) => '<p><em>' + m[1] + '</em></p>' },
    { re: /boldItalicPara\("([^"]+?)"\)/g, type: 'para', fmt: (m) => '<p><strong><em>' + m[1] + '</em></strong></p>' },
    { re: /bullet\("([^"]+?)"\)/g, type: 'bullet', fmt: (m) => '<li>' + m[1] + '</li>' },
    { re: /numberedItem\((\d+),\s*"([^"]+?)"\)/g, type: 'numbered', fmt: (m) => '<p>' + m[1] + '. ' + m[2] + '</p>' },
  ];

  let allMatches = [];
  for (const p of patterns) {
    let mm;
    while ((mm = p.re.exec(block)) !== null) {
      allMatches.push({ pos: mm.index, type: p.type, text: p.fmt(mm) });
    }
  }

  // mixedPara - extract text and build HTML
  const mixedRe = /mixedPara\(\[([\s\S]*?)\]\)/g;
  let m;
  while ((m = mixedRe.exec(block)) !== null) {
    const inner = m[1];
    let htmlParts = [];
    // Parse the array elements - strings and objects
    const tokenRe = /\{\s*text:\s*"([^"]*?)"(?:,\s*(?:bold|italics):\s*(?:true|false))*\s*(?:,\s*(?:bold|italics):\s*(?:true|false))*\s*\}|"([^"]*?)"/g;
    let tm;
    while ((tm = tokenRe.exec(inner)) !== null) {
      const text = tm[1] !== undefined ? tm[1] : tm[2];
      if (!text || text.length < 2) continue;
      // Check if bold/italics in the surrounding context
      const chunk = inner.substring(Math.max(0, tm.index - 5), tm.index + tm[0].length + 30);
      const isBold = /bold:\s*true/.test(chunk);
      const isItalic = /italics:\s*true/.test(chunk);
      if (isBold && isItalic) htmlParts.push('<strong><em>' + text + '</em></strong>');
      else if (isBold) htmlParts.push('<strong>' + text + '</strong>');
      else if (isItalic) htmlParts.push('<em>' + text + '</em>');
      else htmlParts.push(text);
    }
    if (htmlParts.length > 0) {
      allMatches.push({ pos: m.index, type: 'para', text: '<p>' + htmlParts.join('') + '</p>' });
    }
  }

  allMatches.sort((a, b) => a.pos - b.pos);

  // Now split by h2 headings into topics
  let topics = [];
  let currentTopic = null;
  let bulletBuffer = [];

  function flushBullets() {
    if (bulletBuffer.length > 0) {
      if (currentTopic) {
        currentTopic.html += '<ul>' + bulletBuffer.join('') + '</ul>';
      }
      bulletBuffer = [];
    }
  }

  for (const item of allMatches) {
    if (item.type === 'h2') {
      flushBullets();
      // Start new topic
      if (currentTopic) topics.push(currentTopic);
      currentTopic = { title: item.text, html: '' };
    } else if (item.type === 'h3') {
      flushBullets();
      if (currentTopic) {
        currentTopic.html += '<h4>' + item.text + '</h4>';
      }
    } else if (item.type === 'bullet') {
      bulletBuffer.push(item.text);
    } else {
      flushBullets();
      if (currentTopic) {
        currentTopic.html += item.text;
      }
    }
  }
  flushBullets();
  if (currentTopic) topics.push(currentTopic);

  return topics;
}

// Escape for PHP single-quoted string
function phpEscape(str) {
  return str.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}

// Build PHP seeder
let php = `<?php

namespace Database\\Seeders;

use App\\Models\\Course;
use App\\Models\\Module;
use App\\Models\\InformationSheet;
use App\\Models\\Topic;
use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;

class InformationSheetContentSeeder extends Seeder
{
    public function run()
    {
        DB::beginTransaction();

        try {
            $course = Course::firstOrCreate(
                ['course_code' => 'EPAS-NCII'],
                [
                    'course_name' => 'Electronic Products Assembly and Servicing NCII',
                    'description' => 'This course covers the competencies required to assemble and service electronic products according to industry standards.',
                    'sector' => 'Electronics',
                    'is_active' => true,
                    'order' => 1
                ]
            );

            $module = Module::firstOrCreate(
                ['module_number' => 'Module 1', 'course_id' => $course->id],
                [
                    'sector' => 'Electronics',
                    'qualification_title' => 'Electronic Products Assembly And Servicing NCII',
                    'unit_of_competency' => 'Assemble Electronic Products',
                    'module_title' => 'Assembling Electronic Products',
                    'module_name' => 'Competency Based Learning Material',
                    'how_to_use_cblm' => 'Welcome to the Module "Assembling Electronic Products". This module contains training materials and activities for you to complete.

The unit of competency "Assemble Electronic Products" contains the knowledge, skills and attitudes required for Electronic Products Assembly and Servicing course.

You are required to go through a series of learning activities in order to complete each of the learning outcomes of the module.',
                    'introduction' => 'This module contains information sheet(s) and suggested learning activities in Assembling Electronic Products. It includes instructions and procedure on how to Assemble Electronic Products.

This module consists of five (5) learning outcomes.',
                    'learning_outcomes' => 'Upon completion of the module the students shall be able to:
1. Prepare to assemble electronics products
2. Prepare/Make PCB modules
3. Mount and solder electronic components
4. Assemble electronic products
5. Test and inspect assembled electronic products',
                    'is_active' => true,
                    'order' => 1,
                ]
            );

`;

let totalTopics = 0;

for (let i = 0; i < sheets.length; i++) {
  const start = src.indexOf('async function ' + sheets[i]);
  const end = i < 5 ? src.indexOf('async function ' + sheets[i + 1]) : src.indexOf('(async');
  const block = src.substring(start, end);
  const topics = extractTopics(block);

  const escapedTitle = phpEscape(titles[i]);

  // Create info sheet with short summary
  php += `
            // ===== Information Sheet ${sheetNumbers[i]} =====
            $sheet${i + 1} = InformationSheet::updateOrCreate(
                ['module_id' => $module->id, 'sheet_number' => '${sheetNumbers[i]}'],
                [
                    'title' => '${escapedTitle}',
                    'content' => '${phpEscape(titles[i])}',
                    'order' => ${i + 1},
                ]
            );

            // Delete old topics for this sheet
            Topic::where('information_sheet_id', $sheet${i + 1}->id)->delete();

`;

  // Create topics
  for (let t = 0; t < topics.length; t++) {
    const topic = topics[t];
    // Skip non-content sections (self-checks, tasks, jobs, homework, performance criteria)
    const titleLower = topic.title.toLowerCase();
    if (titleLower.startsWith('self check') || titleLower.startsWith('self-check')) continue;
    if (titleLower.startsWith('task sheet') || titleLower.startsWith('job sheet')) continue;
    if (titleLower.startsWith('homework') || titleLower.startsWith('home work')) continue;
    if (titleLower.startsWith('performance criteria')) continue;
    if (topic.html.length === 0) continue;

    totalTopics++;
    const escapedTopicTitle = phpEscape(topic.title);
    const escapedTopicContent = phpEscape(topic.html);

    php += `            Topic::create([
                'information_sheet_id' => $sheet${i + 1}->id,
                'title' => '${escapedTopicTitle}',
                'content' => '${escapedTopicContent}',
                'order' => ${t + 1},
            ]);

`;
  }

  console.log(`Sheet ${sheetNumbers[i]}: ${titles[i]} -> ${topics.length} topics`);
  topics.forEach((t, idx) => console.log(`  ${idx + 1}. ${t.title} (${t.html.length} chars HTML)`));
}

php += `
            DB::commit();
            $this->command->info('Information Sheets 1.1-1.6 with ' . ${totalTopics} . ' topics seeded successfully!');

        } catch (\\Exception $e) {
            DB::rollBack();
            $this->command->error('Seeder failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
`;

fs.writeFileSync(__dirname + '/../database/seeders/InformationSheetContentSeeder.php', php);
console.log(`\nSeeder created! ${totalTopics} total topics. Size: ${php.length} bytes`);
