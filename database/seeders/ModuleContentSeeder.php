<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Module;
use App\Models\InformationSheet;
use App\Models\Topic;
use App\Models\SelfCheck;
use App\Models\SelfCheckQuestion;
use App\Models\TaskSheet;
use App\Models\JobSheet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleContentSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // ── Course ──
            $course = Course::firstOrCreate(
                ['course_code' => 'EPAS-NCII'],
                [
                    'course_name' => 'Electronic Products Assembly and Servicing NCII',
                    'description' => 'This course covers the competencies required to assemble and service electronic products according to industry standards.',
                    'sector' => 'Electronics',
                    'is_active' => true,
                    'order' => 1,
                ]
            );

            // ── Module 1 ──
            $module = Module::firstOrCreate(
                ['module_number' => 'Module 1', 'course_id' => $course->id],
                [
                    'sector' => 'Electronics',
                    'qualification_title' => 'Electronic Products Assembly And Servicing NCII',
                    'unit_of_competency' => 'Assemble Electronic Products',
                    'module_title' => 'Assembling Electronic Products',
                    'module_name' => 'Competency Based Learning Material',
                    'how_to_use_cblm' => 'Welcome to the Module "Assembling Electronic Products". This module contains training materials and activities for you to complete.

The unit of competency "Assemble Electronic Products" contains the knowledge, skills and attitudes required for Electronic Products Assembly and Servicing course Required to obtain the National Certificate(NC) level II.

You are required to go through a series of learning activities in order to complete each of the learning outcomes of the module. In each learning outcome there are Information sheets, information sheets and activity sheets. Do this activity on your own and answer the Self Check at the end of each learning activity.

If you have questions, do not hesitate to ask your teacher for assistance.',
                    'introduction' => 'This module contains information sheet(s) and suggested learning activities in Assembling Electronic Products. It includes instructions and procedure on how to Assemble Electronic Products.

This module consists of five (5) learning outcomes. Learning outcomes contain learning activities supported by instruction sheets.',
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

            // ╔══════════════════════════════════════════════════╗
            // ║  INFORMATION SHEET 1.1                          ║
            // ║  Introduction to Basic Electronics & Electricity ║
            // ╚══════════════════════════════════════════════════╝
            $sheet1 = InformationSheet::updateOrCreate(
                ['module_id' => $module->id, 'sheet_number' => '1.1'],
                [
                    'title' => 'Introduction to Basic Electronics and Electricity',
                    'content' => 'This information sheet covers the fundamental concepts of electronics and electricity, including electric history, free electrons, sources of electricity, alternative energy, types of electric energy and current, and types of materials used in electronics.',
                    'order' => 1,
                ]
            );

            // Clean existing related data for idempotency
            $this->cleanSheetData($sheet1);

            // ── Sheet 1.1 Topics ──
            $this->createTopics($sheet1, [
                [
                    'title' => 'Electric History',
                    'order' => 1,
                    'content' => '<p>For hundreds of years electricity has fascinated many scientists. Around 600 BC, Greek philosophers discovered that by rubbing amber against a cloth, lightweight objects would stick to it. Just like rubbing a balloon on a cloth makes the balloon stick to other objects. It was not until around the year 1600, that any real research was done on this phenomenon. A scientist by the name of Dr. William Gilbert researched the effects of amber and magnets and wrote the theory of magnetism. In fact, Dr. Gilbert was the first to use the word electric in his theory.</p>
<p>Dr. William Gilbert\'s research and theories opened the door for more discoveries into magnetism and the development of electricity.</p>
<p>Electricity is produce when the electrons flow on a conductor.</p>
<p><em>Following Illustrations are those who contribute a lot in the History of Electricity:</em></p>
<p><strong>1. James Watt (1736-1819)</strong></p>
<p>James Watt was a Scottish inventor who made improvements to the steam engine during the late 1700s. Soon, factories and mining companies began to use Watt\'s new-and-improved steam engine for their machinery. This helped jumpstart the Industrial Revolution. After his death, Watt\'s name was used to describe the electrical unit of power.</p>
<p><strong>2. Alessandro Volta (1745-1827)</strong></p>
<p>Using zinc, copper and cardboard, this Italian professor invented the first battery. Volta\'s battery produced a reliable, steady current of electricity. The unit of voltage is now named after Volta.</p>
<p><strong>3. Andre-Marie Ampere (1775-1836)</strong></p>
<p>Andre-Marie Ampere, a French physicist and science teacher, played a big role in discovering electromagnetism. He also helped describe a way to measure the flow of electricity. The ampere, which is the unit for measuring electric current, was named in honour of him.</p>
<p><strong>4. Georg Ohm (1787-1854)</strong></p>
<p>German physicist and teacher Georg Ohm researched the relationship between voltage, current and resistance. In 1827, he proved that the amount of electrical current that can flow through a substance depends on its resistance to electrical flow. This is known as Ohm\'s Law.</p>
<p><strong>5. Michael Faraday (1791-1867)</strong></p>
<p>Michael Faraday, a British physicist and chemist, was the first person to discover that moving a magnet near a coil of copper wire produced an electric current in the wire.</p>
<p><strong>6. Henry Woodward</strong></p>
<p>Henry Woodward, a Canadian medical student, played a major role in developing the electric light bulb. In 1874, Woodward and a colleague named Mathew Evans placed a thin metal rod inside a glass bulb. They forced the air out of the bulb and replaced it with a gas called nitrogen. The rod glowed when an electric current passed through it, creating the first electric lamp. In 1889, they sold their patent to Thomas Edison.</p>
<p><strong>7. Thomas Edison (1847-1931)</strong></p>
<p>American inventor Thomas Edison purchased Henry Woodward\'s patent and began to work on improving the idea. He attached wires to a thin strand of paper, or filament, inside a glass globe. The filament began to glow, which generated some light. This became the first incandescent light bulb.</p>
<p><strong>8. Nikola Tesla (1856-1943)</strong></p>
<p>A Serbian inventor named Nikola Tesla invented the first electric motor by reversing the flow of electricity on Thomas Edison\'s generator. In 1885, he sold his patent rights to the Westinghouse Electric Company. In 1893, the company used Tesla\'s ideas to light the Chicago World\'s Fair with a quarter of a million lights.</p>
<p><strong>9. Sir Adam Beck (1857-1925)</strong></p>
<p>In the early 1900s, manufacturer and politician Sir Adam Beck pointed out that private power companies were charging customers too much for electricity. He worked to get the Ontario government to create the Hydro-Electric Power Commission in 1910, providing inexpensive electricity to many Ontario towns and cities. Because of his efforts, he earned the nickname The Hydro Knight.</p>',
                ],
                [
                    'title' => 'Free Electrons',
                    'order' => 2,
                    'content' => '<p><em>Heat</em> is only one of the types of energy that can cause electrons to be forced from their orbits. A <strong>magnetic field</strong> can also be used to cause electrons to move in a given direction. <em>Light energy</em> and <em>pressure</em> on a crystal are also used to generate electricity by forcing electrons to flow along a given path.</p>
<p>When electrons leave their orbits, they move from atom to atom at random, drifting in no particular direction. Electrons that move in such a way are referred to as free electrons. However, a force can be used to cause them to move in a given direction. That is how electricity (the flow of electrons along a conductor) is generated.</p>
<p>The electrons in the outermost orbit are called <em>valence electrons.</em> If a valence electron acquires a sufficient amount of energy, it can escape from the outer orbit. The escaped valence electron is called a <em>free electron.</em> It can migrate easily from one atom to another.</p>
<p>Maximum numbers of electrons allowed in each shell: 2, 8, 18, 32, 32, 18, 2</p>',
                ],
                [
                    'title' => 'Introduction to Sources of Electricity',
                    'order' => 3,
                    'content' => '<p><strong>Sources of electricity</strong> are everywhere in the world. Worldwide, there is a range of energy resources available to generate electricity. These energy resources fall into two main categories, often called renewable and non-renewable energy resources. Each of these resources can be used as a source to generate electricity, which is a very useful way of transferring energy from one place to another such as to the home or to industry.</p>
<p>There are two Categories of Sources of Electricity:</p>
<p><em>Renewable</em> sources of energy can consider the natural element such as water, volcano and wind that is used to create energy by the help of a turbine and other element that can produce energy by using sunlight.</p>
<p>Non-renewable sources of energy can be divided into two types: fossil fuels and nuclear fuel.</p>',
                ],
                [
                    'title' => 'How is Power Generated?',
                    'order' => 4,
                    'content' => '<p>An electric generator is a device for converting mechanical energy into electrical energy. The process is based on the relationship between magnetism and power. When a wire or any other electrically conductive material moves across a magnetic field, an electric current occurs in the wire.</p>
<p>The large generators used by the electric utility industry have a stationary conductor. A magnet attached to the end of a spinning coil of wire rotating shaft is positioned inside a stationary conducting ring that is wrapped with a long, continuous piece of wire. When the magnet rotates, it induces a small electric current in each section of wire as it passes. All the small currents of individual sections add up to one current of considerable size. This current is used for electric power.</p>',
                ],
                [
                    'title' => 'How are Turbines Used to Generate Power?',
                    'order' => 5,
                    'content' => '<p>An electric utility power station uses either a turbine, engine, water wheel, or other similar machine to drive an electric generator or a device that converts mechanical or chemical energy to power. Steam turbines, internal-combustion engines, gas combustion turbines, water turbines, and wind turbines are the most common methods to generate power.</p>
<p><strong>Most of the power in the United States is produced through the use of steam turbines in power plants.</strong> A turbine converts the kinetic energy of a moving fluid (liquid or gas) to mechanical energy. Steam turbines have a series of blades mounted on a shaft against which steam is forced, thus rotating the shaft connected to the generator. In a fossil-fueled steam turbine, the fuel is burned in a furnace to heat water in a boiler to produce steam.</p>
<p><strong><em>Coal, petroleum (oil), and natural gas</em></strong> are burned in large furnaces to heat water to make steam that in turn pushes on the blades of a turbine.</p>
<p>How it works:</p>
<p>1. Conveyor delivers coal to the coal hopper to be crushed down to 2 inches, then delivered by a conveyor belt to the pulverizer.</p>
<p>2. Pulverizer crushes the coal into a very fine powder, mixed with air and blown into the furnace for combustion.</p>
<p>3. Water Purification - water must be purified before use in the boiler tubes to minimize corrosion.</p>
<p>4. Boiler - inside the boiler, the coal and air ignites instantly. Large amounts of boiler fed water are pumped through tubes. The intense heat vaporizes the feed water to create steam.</p>
<p>5. Precipitator Scrubber captures and removes up to 99.4% of the ash before it reaches the stacks.</p>
<p>6. Stack - remaining flue gases are dissipated into the atmosphere through large stacks.</p>
<p>7. Steam Turbine - the high-pressure steam travels into the turbine blades causing it to spin.</p>
<p>8. Generator - the spinning turbine causes the shaft to turn inside the generator creating electric energy.</p>
<p>9. Transformer - the voltage is increased and the current decreased before the electricity flows into the transmission system.</p>
<p>10. Distribution - substations reduce the voltage for distribution to homes and businesses.</p>
<p>11. Condensers circulate cool water which cools down and condenses the steam after it is used.</p>
<p>12. Cooling pond - warmed water may be reused by cooling in a cooling pond.</p>
<p><em>Natural gas</em> can also be burned to produce hot combustion gases that pass directly through a turbine. In 2000, 16% of the nation\'s power was fueled by natural gas.</p>
<p><em>Petroleum</em> can also be used to make steam to turn a turbine. Petroleum was used to generate less than 3% of all power in U.S. plants in 2000.</p>
<p><strong><em>Nuclear power</em></strong> is a method in which steam is produced by heating water through a process called nuclear fission. In nuclear power plants, a reactor contains a core of nuclear fuel, primarily enriched uranium. When atoms of uranium fuel are hit by neutrons they fission (split), releasing heat and more neutrons. Nuclear power is used to generate 20% of all the country\'s power.</p>
<p>Nuclear energy is energy that is stored in the nucleus or center core of an atom. The nucleus of an atom is made of tiny particles of protons (+ positive charge) and neutrons (no charge). The electrons (- negative charge) move around the nucleus.</p>
<p><strong>1. Fusion</strong> - combining atoms to make a new atom. The energy from the sun is produced by fusion.</p>
<p><strong>2. Fission</strong> - splitting an atom into two smaller atoms. Nuclear power plants use fission to make electricity by splitting <strong>uranium</strong> atoms.</p>
<p><strong><em>Hydropower</em></strong> is the source for 7% of U.S. power generation. Flowing water is used to spin a turbine connected to a generator. There are two basic types: dam-based systems and run-of-river systems.</p>',
                ],
                [
                    'title' => 'Alternative Energy',
                    'order' => 6,
                    'content' => '<p>Alternative Energy comes from resources like the sun (solar), the earth (geothermal), the wind (wind power), wood, agricultural crops and animal waste (biomass), landfill or methane gasses (biogas), and other sources like fuel cells. These resources are abundant and are renewable fuels.</p>
<p><strong><em>Geothermal power</em></strong> comes from heat energy buried beneath the surface of the earth.</p>
<h4>Types of Geothermal Power Plants:</h4>
<ul><li>Dry steam</li><li>Flash steam</li><li>Binary cycle</li></ul>
<p><strong>Geothermal Dry Steam Power Plants</strong> - water extracted from underground reservoirs must be in its gaseous form. Requires steam of at least 150&deg;C (300&deg;F). The first one was constructed in Larderello, Italy, in 1904.</p>
<p><strong>Geothermal Flash Steam Power Plants</strong> - uses water at temperatures of at least 182&deg;C (360&deg;F). High-pressure hot water is flashed (vaporized) into steam inside a flash tank. Today\'s most common geothermal power plant type.</p>
<p><strong>Geothermal Binary Cycle Power Plants</strong> - the water-temperature can be as low as 57&deg;C (135&deg;F). Uses a working fluid (binary fluid) with a much lower boiling temperature than water.</p>
<p><strong><em>Solar power</em></strong> is derived from the energy of the sun. Photovoltaic (PV) conversion generates electric power directly from sunlight in a solar cell. A PV cell consists of two or more thin layers of semi-conducting material, most commonly silicon. When exposed to light, electrical charges are generated.</p>
<p><strong><em>Wind power</em></strong> is derived from the conversion of the energy contained in wind into power.</p>
<p><strong><em>Biomass</em></strong> includes wood, municipal solid waste (garbage), and agricultural waste, such as corn cobs and wheat straw.</p>',
                ],
                [
                    'title' => 'Types of Electric Energy',
                    'order' => 7,
                    'content' => '<p><em>Potential energy</em> (static electricity) is electricity at rest, can be called energy due to position or composition. Examples: flash light batteries, car batteries.</p>
<p><em>Kinetic energy</em> (current electricity/dynamic electricity) is electricity in motion or energy of motion. Example: when electrical charges stored in a battery move or flow to perform useful work.</p>',
                ],
                [
                    'title' => 'Current',
                    'order' => 8,
                    'content' => '<p>Electric current is the flow of electrons, but electrons do not jump directly from the origin point of the current to the destination. Instead, each electron moves a short distance to the next atom, transferring its energy to an electron in that new atom, which jumps to another atom, and so on.</p>
<h4>Types of Electric Current</h4>
<p><strong><em>Direct Current</em></strong> is electric current that only flows in one direction. A common place to find direct current is in batteries. Batteries need direct current to charge up, and will only produce direct current.</p>
<p><strong><em>Alternating Current</em></strong> alternates in direction. Alternating current is used for the production and transportation of electricity. It is easier and cheaper to downgrade high voltage current to lower voltage for home use when the current is AC. In the late 19th century, an industrial struggle between the Westinghouse Company (AC) and General Electric (DC) ended in AC\'s favor when Westinghouse successfully lit the 1893 Chicago World\'s Fair using AC.</p>
<p>Advantages and Disadvantages:</p>
<p><strong>AC</strong> is easy to produce, easy to amplify, but not stable and cannot store.</p>
<p><strong>DC</strong> is not easy to produce, not easy to amplify, but stable and can be stored.</p>',
                ],
                [
                    'title' => 'Conductors, Insulators and Semi-conductors',
                    'order' => 9,
                    'content' => '<p><strong>Conductors</strong> are made of materials that electricity can flow through easily. These materials are made up of atoms whose electrons can move away freely. Gold is considered as best conductor because of its atomic number of elements.</p>
<p>Examples of Conductive materials:</p>
<ul><li>Copper</li><li>Aluminum</li><li>Platinum</li><li>Gold</li><li>Silver</li><li>Water</li><li>People and Animals</li></ul>
<p><strong>Insulators</strong> are materials opposite of conductors. The atoms are not easily freed and are stable, preventing or blocking the flow of electricity.</p>
<p>Examples of Insulating materials:</p>
<ul><li>Glass</li><li>Porcelain</li><li>Plastic</li><li>Rubber</li></ul>
<p>Electricity will always take the shortest path to the ground. Your body is 60% water and that makes you a good <strong>conductor</strong> of electricity.</p>
<p>The rubber or plastic on an electrical cord provides an <strong>insulator</strong> for the wires.</p>',
                ],
                [
                    'title' => 'Semi-Conductors',
                    'order' => 10,
                    'content' => '<p>A semiconductor is a material that has intermediate conductivity between a conductor and an insulator. In a process called doping, small amounts of impurities are added to pure semiconductors causing large changes in the conductivity of the material. Examples include silicon, the basic material used in the integrated circuit, and germanium, the semiconductor used for the first transistors.</p>
<p>Semiconductors materials such as silicon (Si), germanium (Ge) and gallium arsenide (GaAs), have electrical properties somewhere in the middle, between those of a "conductor" and an "insulator". They are not good conductors nor good insulators (hence their name "semi"-conductors).</p>
<p>Their ability to conduct electricity can be greatly improved by adding certain "impurities" to this crystalline structure thereby, producing more free electrons than holes or vice versa. These impurities are called donors or acceptors depending on whether they produce electrons or holes respectively. This process is called <strong>Doping</strong>.</p>
<p>The most commonly used semiconductor material by far is silicon. Silicon has four valence electrons in its outermost shell which it shares with its neighboring silicon atoms to form full orbitals of eight electrons.</p>
<p>Crystals of pure silicon (or germanium) are therefore good insulators, or at the very least very high value resistors.</p>
<p>To extract an electric current from silicon, we need to create a "positive" and a "negative" pole within the silicon allowing electrons and therefore electric current to flow out. These poles are created by doping the silicon with certain impurities.</p>',
                ],
            ]);

            // ── Sheet 1.1 Self Check ──
            $sc11 = SelfCheck::create([
                'information_sheet_id' => $sheet1->id,
                'check_number' => '1.1.1',
                'title' => 'Self Check No. 1.1.1 - Introduction to Electronics and Electricity',
                'description' => 'Test your understanding of basic electronics and electricity concepts.',
                'instructions' => 'Fill in the blanks by choosing the answer. It is shown only on Pre-test but there will be no choices on the Post-test which will be the final.',
                'time_limit' => 30,
                'passing_score' => 70,
                'total_points' => 30,
                'is_active' => true,
            ]);

            $this->createSelfCheckQuestions($sc11, [
                ['question_text' => 'It is the flow of electrons along a conductor', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Electricity', 'order' => 1],
                ['question_text' => 'It is the application of electrical principle', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Electronics', 'order' => 2],
                ['question_text' => 'They discovered Electricity', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Greek philosophers', 'order' => 3],
                ['question_text' => 'It is the material they used to discover the Electricity', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Amber', 'order' => 4],
                ['question_text' => 'It is a device that converts mechanical energy into electrical energy', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Generator', 'order' => 5],
                ['question_text' => 'It is a part of an Atom that moves, and produce current', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Electron', 'order' => 6],
                ['question_text' => 'It is considered as best conductor', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Gold', 'order' => 7],
                ['question_text' => 'Copper has an atomic number of how many?', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => '29', 'order' => 8],
                ['question_text' => 'It is the source of Electricity that uses sunlight from Photovoltaic of the sun energy to electrical energy', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Solar power', 'order' => 9],
                ['question_text' => 'It is the source of Electricity that uses Water\'s flow to provide mechanical energy to electrical energy', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Hydropower', 'order' => 10],
                ['question_text' => 'It is a material which Electricity can pass easily', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Conductor', 'order' => 11],
                ['question_text' => 'It is a process of splitting atoms into smaller atoms that release heat and radiation', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Fission', 'order' => 12],
                ['question_text' => 'If the Electrons on the last orbit/shell is not stable, it is also called ___', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Free electron', 'order' => 13],
                ['question_text' => 'It is an electronic device that converts AC to DC', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Rectifier', 'order' => 14],
                ['question_text' => 'Give the 3 parts of an Atom (Part 1)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Proton', 'order' => 15],
                ['question_text' => 'Give the 3 parts of an Atom (Part 2)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Neutron', 'order' => 16],
                ['question_text' => 'Give the 3 parts of an Atom (Part 3)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Electron', 'order' => 17],
                ['question_text' => 'Give the 3 types of Materials (Type 1)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Conductor', 'order' => 18],
                ['question_text' => 'Give the 3 types of Materials (Type 2)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Insulator', 'order' => 19],
                ['question_text' => 'Give the 3 types of Materials (Type 3)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Semiconductor', 'order' => 20],
                ['question_text' => 'Give the 2 categories of current (Category 1, complete name)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Direct Current', 'order' => 21],
                ['question_text' => 'Give the 2 categories of current (Category 2, complete name)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Alternating Current', 'order' => 22],
                ['question_text' => 'Give at least 4 Sources of Electricity (Source 1)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Solar', 'order' => 23],
                ['question_text' => 'Give at least 4 Sources of Electricity (Source 2)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Hydropower', 'order' => 24],
                ['question_text' => 'Give at least 4 Sources of Electricity (Source 3)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Nuclear', 'order' => 25],
                ['question_text' => 'Give at least 4 Sources of Electricity (Source 4)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Geothermal', 'order' => 26],
                ['question_text' => 'Give the 2 advantages of AC (Advantage 1)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Easy to produce', 'order' => 27],
                ['question_text' => 'Give the 2 advantages of AC (Advantage 2)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Easy to amplify', 'order' => 28],
                ['question_text' => 'Give the 2 advantages of DC (Advantage 1)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Stable', 'order' => 29],
                ['question_text' => 'Give the 2 advantages of DC (Advantage 2)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Can be stored', 'order' => 30],
            ]);

            // ╔══════════════════════════════════════════════════╗
            // ║  INFORMATION SHEET 1.2                          ║
            // ║  Resistors, Color Coding, Ohm's Law, Circuits   ║
            // ╚══════════════════════════════════════════════════╝
            $sheet2 = InformationSheet::updateOrCreate(
                ['module_id' => $module->id, 'sheet_number' => '1.2'],
                [
                    'title' => 'Resistors, Color Coding, Conversion, Tolerance, Circuits and Ohm\'s Law',
                    'content' => 'This section covers resistors, their color coding, tolerance computation, testing using a multi-tester, Ohm\'s Law, electrical circuits, and different types of circuits.',
                    'order' => 2,
                ]
            );

            $this->cleanSheetData($sheet2);

            // ── Sheet 1.2 Topics ──
            $this->createTopics($sheet2, [
                [
                    'title' => 'Electronic Components: Resistors',
                    'order' => 1,
                    'content' => '<p>A <strong>resistor</strong> is an electronic device that limits or opposes the amount of current in a circuit. A resistor has two terminals across which electricity must pass, and is designed to drop the voltage of the current as it flows from one terminal to the next. A resistor is primarily used to create and maintain a known safe current within an electrical component.</p>
<p>Every resistor falls into one of two categories: fixed or variable.</p>
<p>1. Fixed resistor - has a predetermined amount of resistance to current</p>
<p>2. Variable resistor - can be adjusted to give different levels of resistance. Examples are the carbon composition and wirewound resistors. Variable resistors are also called potentiometers and are commonly used as volume controls on audio devices.</p>
<h4>Resistor Color Coding (4 Bands)</h4>
<table><tr><th>Band Color</th><th>Digit</th><th>Multiplier</th><th>Tolerance</th></tr>
<tr><td>Black</td><td>0</td><td>1</td><td></td></tr>
<tr><td>Brown</td><td>1</td><td>10</td><td>&plusmn;1%</td></tr>
<tr><td>Red</td><td>2</td><td>100</td><td>&plusmn;2%</td></tr>
<tr><td>Orange</td><td>3</td><td>1,000</td><td></td></tr>
<tr><td>Yellow</td><td>4</td><td>10,000</td><td>&plusmn;4%</td></tr>
<tr><td>Green</td><td>5</td><td>100,000</td><td></td></tr>
<tr><td>Blue</td><td>6</td><td>1,000,000</td><td></td></tr>
<tr><td>Violet</td><td>7</td><td>10,000,000</td><td></td></tr>
<tr><td>Grey</td><td>8</td><td>100,000,000</td><td></td></tr>
<tr><td>White</td><td>9</td><td></td><td></td></tr>
<tr><td>Gold</td><td></td><td>0.1</td><td>&plusmn;5%</td></tr>
<tr><td>Silver</td><td></td><td>0.01</td><td>&plusmn;10%</td></tr>
<tr><td>None</td><td></td><td></td><td>&plusmn;20%</td></tr></table>
<p>The color codes are read from left to right, with the tolerance band oriented to the right side. Match the color of the first band to its associated number under the digit column. This is the first digit. Repeat for the second band. The third band is the multiplier.</p>',
                ],
                [
                    'title' => 'Tolerance',
                    'order' => 2,
                    'content' => '<p>Tolerance is the maximum and minimum accepted value of a resistor when measuring it through a multi-tester. It is calculated by multiplying the percentage of the fourth or fifth color value of the resistor on the first and second color values.</p>
<h4>Example: Brown, Black, Red, Gold (5% tolerance)</h4>
<p>Resistor value: 1000&Omega;</p>
<p>1000 &times; 0.05 = 50</p>
<p>Maximum tolerance: 1000 + 50 = 1050&Omega;</p>
<p>Minimum tolerance: 1000 - 50 = 950&Omega;</p>
<h4>Example: Brown, Black, Red, Silver (10% tolerance)</h4>
<p>Resistor value: 1000&Omega;</p>
<p>1000 &times; 0.1 = 100</p>
<p>Maximum tolerance: 1000 + 100 = 1100&Omega;</p>
<p>Minimum tolerance: 1000 - 100 = 900&Omega;</p>',
                ],
                [
                    'title' => 'Ohm\'s Law',
                    'order' => 3,
                    'content' => '<p>Ohm\'s Law states that an electrical circuit\'s current is directly proportional to its voltage, and inversely proportional to its resistance. So, if voltage increases, the current will also increase, and if resistance increases, current decreases.</p>
<p>The formula for Ohm\'s Law is:</p>
<p><strong>I = V/R &nbsp;&nbsp; V = I &times; R &nbsp;&nbsp; R = V/I</strong></p>
<p>where: V = voltage in volts, I = current in amperes, R = resistance in ohms</p>
<p>In the first version (I = V/R), the current is directly proportional to the voltage and inversely proportional to the resistance.</p>
<p>The second version tells us voltage can be calculated if current and resistance are known.</p>
<p>The third version tells us resistance can be calculated if voltage and current are known.</p>
<h4>Example:</h4>
<p>Voltage = 100 volts, Resistance = 100&Omega;, Current = ?</p>
<p>Answer: I = V/R = 100/100 = 1 ampere</p>',
                ],
                [
                    'title' => 'Electrical Circuits',
                    'order' => 4,
                    'content' => '<p>An electrical circuit is a device that uses electricity to perform a task. The circuit is a closed loop formed by a power source, wires, a fuse, a load, and a switch.</p>
<h4>Types of Circuits</h4>
<p>A <strong>Series Circuit</strong> is the simplest because it has only one possible path that the electrical current may flow; if the electrical circuit is broken, none of the load devices will work.</p>
<p>In the <strong>Parallel Circuit</strong>, the electricity can travel to two or more paths. If one path fails to function, electricity can still flow back through other paths.</p>
<p>A <strong>Series-Parallel Circuit</strong> is a combination of the first two. It attaches some loads to a series circuit and others to parallel circuits.</p>
<h4>Formulas</h4>
<p><strong>Series Circuit:</strong> R_total = R1 + R2 + R3</p>
<p><strong>Parallel Circuit:</strong> R_total = 1 / (1/R1 + 1/R2 + 1/R3)</p>
<p><strong>Formula for 2 Parallel Resistors only:</strong> R_T = (R1 &times; R2) / (R1 + R2)</p>
<h4>Series Circuit Example</h4>
<p>Three resistors of different resistances sharing a single connection point. When added together the total resistance is 18k&Omega;.</p>
<h4>Parallel Circuit Example</h4>
<p>R_T = 1 / (0.2 + 0.0714 + 0.0476 + 0.04 + 0.01) = 1/0.369 = 2.7&Omega;</p>
<h4>Series-Parallel Circuit Example</h4>
<p>R_P1 = (R1 &times; R2) / (R1 + R2) = (37 &times; 24) / (37 + 24) = 416/61 = 11.05&Omega;</p>
<p>R_T = 11.05 + R3 + R4 = 11.05 + 58 = ~75&Omega;</p>',
                ],
            ]);

            // ── Sheet 1.2 Self Checks ──
            $sc121 = SelfCheck::create([
                'information_sheet_id' => $sheet2->id,
                'check_number' => '1.2.1',
                'title' => 'Self Check No. 1.2.1 - Multiple Choice',
                'description' => 'Resistors, Color coding, Conversion, Tolerance, Circuits and Ohm\'s Law',
                'instructions' => 'Choose the letter of the correct answer.',
                'time_limit' => 10,
                'passing_score' => 70,
                'total_points' => 5,
                'is_active' => true,
            ]);

            $this->createSelfCheckQuestions($sc121, [
                ['question_text' => 'It is an electronic device that resists, limits or opposes the amount of current in a circuit:', 'question_type' => 'multiple_choice', 'points' => 1, 'correct_answer' => 'a', 'options' => ['Resistor', 'Capacitor', 'Diode', 'None of the above'], 'order' => 1],
                ['question_text' => 'A kind of resistor that can vary the resistance:', 'question_type' => 'multiple_choice', 'points' => 1, 'correct_answer' => 'd', 'options' => ['Fixed resistor', 'Potentiometer', 'Volume control', 'Variable resistor'], 'order' => 2],
                ['question_text' => 'A circuit with only one possible path that the electrical current may flow:', 'question_type' => 'multiple_choice', 'points' => 1, 'correct_answer' => 'b', 'options' => ['Parallel', 'Series', 'Series - Parallel', 'None of the above'], 'order' => 3],
                ['question_text' => 'The computation of accepted value of resistor\'s resistance from minimum to maximum:', 'question_type' => 'multiple_choice', 'points' => 1, 'correct_answer' => 'c', 'options' => ['Decoding', 'Conversion', 'Tolerance', 'None of the above'], 'order' => 4],
                ['question_text' => 'It states that an electrical circuit\'s current is directly proportional to its voltage, and inversely proportional to its resistance:', 'question_type' => 'multiple_choice', 'points' => 1, 'correct_answer' => 'b', 'options' => ['Fuse law', 'Ohm\'s law', 'Volt\'s law', 'None of the above'], 'order' => 5],
            ]);

            $sc122 = SelfCheck::create([
                'information_sheet_id' => $sheet2->id,
                'check_number' => '1.2.2',
                'title' => 'Self Check No. 1.2.2 - Color Coding Table',
                'description' => 'Resistors, Color coding, Conversion, Tolerance, Circuits and Ohm\'s Law',
                'instructions' => 'Write and illustrate the resistor\'s color coding table in exact order.',
                'time_limit' => 15,
                'passing_score' => 70,
                'total_points' => 10,
                'is_active' => true,
            ]);

            $this->createSelfCheckQuestions($sc122, [
                ['question_text' => 'Write the complete resistor color coding table in exact order (Color, Digit, Multiplier, Tolerance).', 'question_type' => 'essay', 'points' => 10, 'correct_answer' => 'Black-0-1, Brown-1-10-1%, Red-2-100-2%, Orange-3-1000, Yellow-4-10000-4%, Green-5-100000, Blue-6-1000000, Violet-7-10000000, Grey-8-100000000, White-9, Gold-0.1-5%, Silver-0.01-10%, None-20%', 'order' => 1],
            ]);

            $sc123 = SelfCheck::create([
                'information_sheet_id' => $sheet2->id,
                'check_number' => '1.2.3',
                'title' => 'Self Check No. 1.2.3 - Color Coding, Decoding and Conversion',
                'description' => 'Resistor Color Coding, Decoding and Conversion',
                'instructions' => 'Give the given value of each color of resistor 1-5 and give the color of each given value of resistor 6-10.',
                'time_limit' => 20,
                'passing_score' => 70,
                'total_points' => 10,
                'is_active' => true,
            ]);

            $this->createSelfCheckQuestions($sc123, [
                ['question_text' => 'Brown, Black, Red, Gold - What is the resistance value?', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => '1000 ohms or 1K ohms +/-5%', 'order' => 1],
                ['question_text' => 'Green, Blue, Yellow, Gold - What is the resistance value?', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => '560000 ohms or 560K ohms +/-5%', 'order' => 2],
                ['question_text' => 'Violet, Red, Orange, Silver - What is the resistance value?', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => '72000 ohms or 72K ohms +/-10%', 'order' => 3],
                ['question_text' => 'Yellow, White, Gold, Gold - What is the resistance value?', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => '4.9 ohms +/-5%', 'order' => 4],
                ['question_text' => 'Orange, Orange, Brown, Gold - What is the resistance value?', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => '330 ohms +/-5%', 'order' => 5],
                ['question_text' => '680 K&Omega; &plusmn; 5% - What are the color bands?', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Blue, Grey, Yellow, Gold', 'order' => 6],
                ['question_text' => '26 K&Omega; &plusmn; 10% - What are the color bands?', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Red, Blue, Orange, Silver', 'order' => 7],
                ['question_text' => '3.8 M&Omega; &plusmn; 5% - What are the color bands?', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Orange, Grey, Green, Gold', 'order' => 8],
                ['question_text' => '4.7 K&Omega; &plusmn; 10% - What are the color bands?', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Yellow, Violet, Red, Silver', 'order' => 9],
                ['question_text' => '30 &Omega; &plusmn; 5% - What are the color bands?', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Orange, Black, Black, Gold', 'order' => 10],
            ]);

            $sc124 = SelfCheck::create([
                'information_sheet_id' => $sheet2->id,
                'check_number' => '1.2.4',
                'title' => 'Self Check No. 1.2.4 - Resistor Tolerance',
                'description' => 'Resistor\'s Tolerance computation',
                'instructions' => 'Give all the details of each Resistor, identify the color and value, write down the minimum and maximum tolerance (1 pt. each).',
                'time_limit' => 20,
                'passing_score' => 70,
                'total_points' => 10,
                'is_active' => true,
            ]);

            $this->createSelfCheckQuestions($sc124, [
                ['question_text' => 'Red, Blue, Red, Gold, Silver - Give the value and min/max tolerance', 'question_type' => 'essay', 'points' => 1, 'correct_answer' => '2600 ohms, 5% tolerance: Min 2470, Max 2730; 10% tolerance: Min 2340, Max 2860', 'order' => 1],
                ['question_text' => 'Gray, Orange, Yellow, Gold - Give the value and min/max tolerance', 'question_type' => 'essay', 'points' => 1, 'correct_answer' => '830000 ohms or 830K ohms, 5% tolerance: Min 788500, Max 871500', 'order' => 2],
                ['question_text' => 'White, Blue, Brown, Gold - Give the value and min/max tolerance', 'question_type' => 'essay', 'points' => 1, 'correct_answer' => '960 ohms, 5% tolerance: Min 912, Max 1008', 'order' => 3],
                ['question_text' => 'Blue, Black, Gold, Gold - Give the value and min/max tolerance', 'question_type' => 'essay', 'points' => 1, 'correct_answer' => '6.0 ohms, 5% tolerance: Min 5.7, Max 6.3', 'order' => 4],
                ['question_text' => 'Violet, Red, Gold, Silver - Give the value and min/max tolerance', 'question_type' => 'essay', 'points' => 1, 'correct_answer' => '7.2 ohms, 10% tolerance: Min 6.48, Max 7.92', 'order' => 5],
                ['question_text' => '940 K&Omega; &plusmn; 5% - Give the min/max tolerance', 'question_type' => 'essay', 'points' => 1, 'correct_answer' => 'Min 893000, Max 987000', 'order' => 6],
                ['question_text' => '77 K&Omega; &plusmn; 10% - Give the min/max tolerance', 'question_type' => 'essay', 'points' => 1, 'correct_answer' => 'Min 69300, Max 84700', 'order' => 7],
                ['question_text' => '56 M&Omega; &plusmn; 5% - Give the min/max tolerance', 'question_type' => 'essay', 'points' => 1, 'correct_answer' => 'Min 53200000, Max 58800000', 'order' => 8],
                ['question_text' => '4.7 K&Omega; &plusmn; 10% - Give the min/max tolerance', 'question_type' => 'essay', 'points' => 1, 'correct_answer' => 'Min 4230, Max 5170', 'order' => 9],
                ['question_text' => '87 &Omega; &plusmn; 5% - Give the min/max tolerance', 'question_type' => 'essay', 'points' => 1, 'correct_answer' => 'Min 82.65, Max 91.35', 'order' => 10],
            ]);

            // ── Sheet 1.2 Task Sheet ──
            $ts121 = TaskSheet::create([
                'information_sheet_id' => $sheet2->id,
                'task_number' => '1.2.1',
                'title' => 'Checking and Testing of Resistor',
                'description' => 'Hands-on activity for checking and testing resistors using a multi-tester.',
                'instructions' => '1. Decode the color thru value
2. Compute the minimum and maximum tolerance
3. Using the suggested ohm\'s range, check using the multi-meter
4. Write down the parts, description (R1,R2,etc.), actual reading (resistance), the range use and the remarks if good or defective
5. Submit to Trainer for checking',
                'estimated_duration' => 30,
                'difficulty_level' => 'beginner',
            ]);

            DB::table('task_sheet_objectives')->insert([
                ['task_sheet_id' => $ts121->id, 'objective' => 'Understand the value of color coded resistor', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['task_sheet_id' => $ts121->id, 'objective' => 'Maximize the use of Multi-meter', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['task_sheet_id' => $ts121->id, 'objective' => 'Identify the conditions of a resistor', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ]);

            DB::table('task_sheet_materials')->insert([
                ['task_sheet_id' => $ts121->id, 'material_name' => 'Various resistors', 'quantity' => '5', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['task_sheet_id' => $ts121->id, 'material_name' => 'Multi-tester', 'quantity' => '1', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['task_sheet_id' => $ts121->id, 'material_name' => 'Answer sheet', 'quantity' => '1', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ]);

            // ╔══════════════════════════════════════════════════╗
            // ║  INFORMATION SHEET 1.3                          ║
            // ║  Capacitors and Diodes                          ║
            // ╚══════════════════════════════════════════════════╝
            $sheet3 = InformationSheet::updateOrCreate(
                ['module_id' => $module->id, 'sheet_number' => '1.3'],
                [
                    'title' => 'Capacitors and Diodes',
                    'content' => 'This section covers capacitors, their types, testing, and diodes including their types, testing using a multi-tester, and rectification.',
                    'order' => 3,
                ]
            );

            $this->cleanSheetData($sheet3);

            // ── Sheet 1.3 Topics ──
            $this->createTopics($sheet3, [
                [
                    'title' => 'Devices: Capacitors',
                    'order' => 1,
                    'content' => '<p>A <strong>capacitor</strong> is an electronic device or component that stores or accumulates electrical energy in a circuit. A capacitor consists of two conductive plates, each hosting an opposite charge, separated by a dielectric or insulator.</p>
<p><em>Farad</em> is the measured unit of capacitance. The higher the farad, the longer it holds the stored charges; the lower the farad, the shorter it holds the stored charges.</p>
<p>Capacitance is dependent upon:</p>
<ul><li>Thickness of dielectric</li><li>Area of the plates</li><li>Constant of dielectric</li></ul>
<h4>Functions of Capacitors:</h4>
<p><em>Coupling</em> - to prevent DC from entering the circuit.</p>
<p><em>De-coupling</em> - to prevent or protect the circuit from another circuit.</p>
<p><em>Noise filters or Snubbers</em> - to protect the circuit from distortion and interference.</p>
<p><em>Motor starter</em> - is used for starting the motor.</p>
<p><em>Tuned-in Circuit</em> - is used for tuning circuit.</p>
<h4>Types of Capacitors:</h4>
<ul><li>Polystyrene (Film capacitor)</li><li>Mylar (for high voltage charge)</li><li>Polyethylene (Film capacitor)</li><li>Mica (for high voltage charge)</li><li>Tantalum (electrolytic type)</li><li>Ceramic (for small amount of charge)</li></ul>
<p><strong>Electrolytic capacitors</strong> characteristics: High capacitance-to-size ratio; Polarity sensitivity with terminals marked + and -; Allow more leakage current than other types; Have their C value and voltage rating printed on them.</p>
<h4>Variable Capacitor</h4>
<p>A variable capacitor allows the amount of electrical charge it can hold to be altered over a certain range. Two types include air variable capacitors and vacuum variable capacitors.</p>
<h4>Testing Capacitors with Multi-tester:</h4>
<p><strong>Good Capacitor:</strong> Ohmmeter R x 1, Forward Resistance - Low, Capacitance depends on value.</p>
<p><strong>Shorted Capacitor:</strong> Range R x 1, Forward Resistance - Zero.</p>
<p><strong>Open Capacitor:</strong> Range R x 10, Forward Resistance - Infinite.</p>
<p><strong>Leaky Capacitor:</strong> Range R x 10, Forward Resistance - Zero.</p>',
                ],
                [
                    'title' => 'Devices: Diode',
                    'order' => 2,
                    'content' => '<p>A diode is a semi-conductor device that permits the flow of current in only one direction. A Diode can convert electric current from AC to DC. This is called <strong>Rectification</strong>.</p>
<p>A diode has 2 parts: Anode (A) + and Cathode (K) -</p>
<h4>Types of Diode:</h4>
<p><strong>1. Rectifier Diode</strong> - Converts AC to DC.</p>
<p><strong>2. Signal Diode</strong> - Has much lower power and current ratings (around 150mA, 500mW maximum). Can function better in high frequency applications or in clipping and switching applications. Converts Intermediate Frequency (IF) to Audio Frequency (AF).</p>
<p><strong>3. Regulator Diode</strong> - Used for voltage regulation.</p>
<p><strong>4. Temperature Dependent Diode</strong> - Used for temperatures hot or cold to automatically on and off.</p>
<p><strong>5. Light Emitting Diode (LED)</strong> - A semiconductor light source used as indicator lamps and for lighting.</p>
<p><strong>6. Photodiode (Photosensitive diode)</strong> - A photodetector capable of converting light into either current or voltage. Solar cells are large area photodiodes.</p>
<h4>Testing Diodes with Multi-tester:</h4>
<p><strong>Good Diode:</strong> Forward Resistance - LOW (Not zero/not infinite), Reverse Resistance - Infinite.</p>
<p><strong>Shorted Diode:</strong> Forward Resistance - Zero, Reverse Resistance - Zero.</p>
<p><strong>Open Diode:</strong> Forward Resistance - Infinite, Reverse Resistance - Infinite.</p>
<p><strong>Leaky Diode:</strong> Forward Resistance - Zero, Reverse Resistance - Zero (Shorted).</p>',
                ],
            ]);

            // ── Sheet 1.3 Self Check ──
            $sc131 = SelfCheck::create([
                'information_sheet_id' => $sheet3->id,
                'check_number' => '1.3.1',
                'title' => 'Self Check No. 1.3.1 - Capacitors and Diodes',
                'description' => 'Test your knowledge of capacitors and diodes.',
                'instructions' => 'Part 1: Enumeration. Part 2: Write down the condition of each measured component as leaky, shorted, open or good (2 pts. each).',
                'time_limit' => 15,
                'passing_score' => 70,
                'total_points' => 20,
                'is_active' => true,
            ]);

            $this->createSelfCheckQuestions($sc131, [
                ['question_text' => 'Give at least 4 kinds of diodes (Diode 1)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Rectifier Diode', 'order' => 1],
                ['question_text' => 'Give at least 4 kinds of diodes (Diode 2)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Signal Diode', 'order' => 2],
                ['question_text' => 'Give at least 4 kinds of diodes (Diode 3)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Regulator Diode', 'order' => 3],
                ['question_text' => 'Give at least 4 kinds of diodes (Diode 4)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Light Emitting Diode (LED)', 'order' => 4],
                ['question_text' => 'Give at least 4 kinds of capacitors (Capacitor 1)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Polystyrene', 'order' => 5],
                ['question_text' => 'Give at least 4 kinds of capacitors (Capacitor 2)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Mylar', 'order' => 6],
                ['question_text' => 'Give at least 4 kinds of capacitors (Capacitor 3)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Ceramic', 'order' => 7],
                ['question_text' => 'Give at least 4 kinds of capacitors (Capacitor 4)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Tantalum', 'order' => 8],
                ['question_text' => '2 parts of Diode (Part 1)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Anode', 'order' => 9],
                ['question_text' => '2 parts of Diode (Part 2)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Cathode', 'order' => 10],
            ]);

            // ── Sheet 1.3 Task Sheets ──
            $ts131 = TaskSheet::create([
                'information_sheet_id' => $sheet3->id,
                'task_number' => '1.3.1',
                'title' => 'Checking and Testing of Capacitor',
                'description' => 'Hands-on activity for checking and testing capacitors.',
                'instructions' => '1. Check the value of capacitor
2. Check the polarity of the terminal
3. Using the suggested range in testing a capacitor
4. Use the tester in Reverse bias
5. Write down the parts, description (C1,C2,etc.), findings, the range use and the remarks if good or defective such as leaky, shorted or open.
6. Submit to Trainer for checking',
                'estimated_duration' => 30,
                'difficulty_level' => 'beginner',
            ]);

            DB::table('task_sheet_objectives')->insert([
                ['task_sheet_id' => $ts131->id, 'objective' => 'Understand the value of capacitor', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['task_sheet_id' => $ts131->id, 'objective' => 'Intensify the use of Multi-meter', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['task_sheet_id' => $ts131->id, 'objective' => 'Identify the conditions of a capacitor', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ]);

            $ts132 = TaskSheet::create([
                'information_sheet_id' => $sheet3->id,
                'task_number' => '1.3.2',
                'title' => 'Checking and Testing of Diode',
                'description' => 'Hands-on activity for checking and testing diodes.',
                'instructions' => '1. Check the type of diode
2. Check the polarity of the terminal
3. Using the multi-meter, check by performing both reverse and forward bias
4. Write down the parts, description (D1,D2,etc.), findings, the range use and the remarks if good or defective such as leaky, shorted or open.
5. Submit to Trainer for checking',
                'estimated_duration' => 30,
                'difficulty_level' => 'beginner',
            ]);

            DB::table('task_sheet_objectives')->insert([
                ['task_sheet_id' => $ts132->id, 'objective' => 'Understand the value of diode', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['task_sheet_id' => $ts132->id, 'objective' => 'Intensify the use of Multi-meter', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['task_sheet_id' => $ts132->id, 'objective' => 'Identify the conditions of a diode', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ]);

            // ╔══════════════════════════════════════════════════╗
            // ║  INFORMATION SHEET 1.4                          ║
            // ║  Transistors, ICs and Transformers               ║
            // ╚══════════════════════════════════════════════════╝
            $sheet4 = InformationSheet::updateOrCreate(
                ['module_id' => $module->id, 'sheet_number' => '1.4'],
                [
                    'title' => 'Transistors, Integrated Circuits (ICs) and Transformers',
                    'content' => 'This section covers transistors, integrated circuits, transformers, power supply systems, rectifiers, and voltage regulators.',
                    'order' => 4,
                ]
            );

            $this->cleanSheetData($sheet4);

            // ── Sheet 1.4 Topics ──
            $this->createTopics($sheet4, [
                [
                    'title' => 'Devices: Transistor',
                    'order' => 1,
                    'content' => '<p>A transistor is an electronic semi-device which provides <em>oscillation, amplification, switching</em> and <em>rectification</em> of electrical current. The principal materials used are germanium and silicon.</p>
<p><em>Oscillation</em> - A process of moving back and forth, to and from.</p>
<p><em>Amplification</em> - A process of converting/increasing signal strength.</p>
<p>Two kinds of transistors:</p>
<p>1. Positive Negative Positive (PNP)</p>
<p>2. Negative Positive Negative (NPN)</p>
<p>A PNP transistor is made by sandwiching a slab of silicon with "N-type" impurities between 2 layers of "P-type" semiconductor material. Silicon is generally used for NPN bipolar type. Germanium is used for some power type transistors.</p>
<p>Transistors are composed of three parts:</p>
<p>B - Base - Input (gate controller)</p>
<p>C - Collector - Output (larger electrical input)</p>
<p>E - Emitter - Ground (outlet for supply)</p>
<h4>Silicon Controlled Rectifier (SCR)</h4>
<p>A <strong>silicon-controlled rectifier</strong> is a four-layer solid state current controlling device. SCRs are unidirectional devices (can conduct current only in one direction) as opposed to TRIACs which are bidirectional.</p>',
                ],
                [
                    'title' => 'Devices: Integrated Circuit (IC)',
                    'order' => 2,
                    'content' => '<p>Integrated Circuits (ICs) are used in all types of modern electronic devices. They are integrated, meaning they are made as a total circuit and housed in one enclosure.</p>
<p>Two major kinds of ICs:</p>
<p>1. Analog (or linear) - used as amplifiers, timers and oscillators</p>
<p>2. Digital (or logic) - used in microprocessors and memories</p>
<p>Three categories of IC packages:</p>
<p>1. Small scale integration (SSI) - fewer than 200 components</p>
<p>2. Medium scale Integration (MSI) - 1000 to 256,000 components</p>
<p>3. Large scale integration (LSI) - 256,000 or more components</p>
<h4>IC Package Types:</h4>
<p><strong>Dual In-line Package (DIP) IC</strong> - rectangular housing with two parallel rows of electrical connecting pins. Can be through-hole mounted or inserted in a socket.</p>
<p><strong>Linear IC</strong> - solid-state analog device with theoretically infinite number of operating states, operating over a continuous range of input levels.</p>
<p><strong>Ball Grid Array (BGA) IC</strong> - surface-mount packaging used to permanently mount devices such as microprocessors.</p>
<p><strong>Surface-Mounted Device (SMD) IC</strong> - components mounted directly onto the surface of printed circuit boards (PCBs).</p>',
                ],
                [
                    'title' => 'Devices: Transformers and Power Supply',
                    'order' => 3,
                    'content' => '<h4>Types of Power Supply</h4>
<p>A power supply can be broken down into blocks:</p>
<p><strong>Block Diagram:</strong> 220V AC Mains &rarr; Transformer &rarr; Rectifier &rarr; Smoothing &rarr; Regulator &rarr; Regulated 5V DC</p>
<p><em>Transformer</em> - steps down high voltage AC mains to low voltage AC.</p>
<p><em>Rectifier</em> - converts AC to DC, but the DC output is varying.</p>
<p><em>Smoothing</em> - smooths the DC from varying greatly to a small ripple.</p>
<p><em>Regulator</em> - eliminates ripple by setting DC output to a fixed voltage.</p>
<h4>Transformer</h4>
<p>Transformers convert AC electricity from one voltage to another with little loss of power. Step up transformers increase voltage, step-down transformers reduce voltage.</p>
<p><strong>3 Parts of Transformer:</strong> Core, Primary, Secondary</p>
<p>The ratio of the number of turns on each coil (turn\'s ratio) determines the ratio of the voltages.</p>
<h4>Kinds of Transformer:</h4>
<p>1. Power Transformer 2. Isolation Transformer 3. Auto Transformer 4. Audio Transformer 5. RF and IF Transformer</p>
<p>Types of Power Transformer: Center Tap Transformer, Multi Tap Transformer</p>
<h4>Rectifier</h4>
<p><strong>Bridge rectifier</strong> - uses four diodes, produces full-wave varying DC. 1.4V is used up (each diode uses 0.7V and two are always conducting).</p>
<p><strong>Single diode rectifier</strong> - produces half-wave varying DC which has gaps when AC is negative.</p>
<h4>Smoothing</h4>
<p>Performed by a large value <strong>electrolytic capacitor</strong> connected across the DC supply to act as a reservoir.</p>
<h4>Regulator</h4>
<p>Voltage regulator ICs are available with fixed (usually 5, 12 and 15V) or variable output voltages. Example: 7805 +5V 1A regulator. Many include overload protection and thermal protection.</p>
<h4>Dual Supplies</h4>
<p>Some circuits require positive and negative outputs as well as 0V. Example: a 15V dual supply has +9V, 0V and -9V outputs.</p>',
                ],
            ]);

            // ── Sheet 1.4 Self Check ──
            $sc141 = SelfCheck::create([
                'information_sheet_id' => $sheet4->id,
                'check_number' => '1.4.1',
                'title' => 'Self Check No. 1.4.1 - Transistors, ICs and Transformers',
                'description' => 'Transistors, Integrated Circuits (ICs) and Transformers',
                'instructions' => 'Choose the letter of the correct answer for items 1-5. Answer the enumeration questions for items 6-15.',
                'time_limit' => 15,
                'passing_score' => 70,
                'total_points' => 15,
                'is_active' => true,
            ]);

            $this->createSelfCheckQuestions($sc141, [
                ['question_text' => 'It is a semi conductor device that can be used for amplification, oscillation, rectification and switching', 'question_type' => 'multiple_choice', 'points' => 1, 'correct_answer' => 'a', 'options' => ['Transistor', 'Capacitor', 'Diode', 'None of the above'], 'order' => 1],
                ['question_text' => 'A type of IC with a rectangular housing and two parallel rows of electrical connecting pin', 'question_type' => 'multiple_choice', 'points' => 1, 'correct_answer' => 'b', 'options' => ['Linear IC', 'Dual In-line Package (DIP) IC', 'Surface Mounted Device (SMD) IC', 'None of the above'], 'order' => 2],
                ['question_text' => 'An electro-magnetic device that is commonly used for step-down of voltage', 'question_type' => 'multiple_choice', 'points' => 1, 'correct_answer' => 'c', 'options' => ['Power supply', 'Integrated circuit', 'Transformer', 'None of the above'], 'order' => 3],
                ['question_text' => 'All types of modern electronic devices. They are integrated, meaning they are made as a total circuit and housed in one enclosure', 'question_type' => 'multiple_choice', 'points' => 1, 'correct_answer' => 'b', 'options' => ['Power supply', 'Integrated circuit', 'Transistor', 'None of the above'], 'order' => 4],
                ['question_text' => 'It is a process of a transistor, that allows to move back and forth', 'question_type' => 'multiple_choice', 'points' => 1, 'correct_answer' => 'c', 'options' => ['Amplification', 'Rectification', 'Oscillation', 'None of the above'], 'order' => 5],
                ['question_text' => 'Give at least two (2) types of integrated circuit (Type 1)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Analog (or Linear)', 'order' => 6],
                ['question_text' => 'Give at least two (2) types of integrated circuit (Type 2)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Digital (or Logic)', 'order' => 7],
                ['question_text' => 'What are the three (3) parts of Transistor? (Part 1)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Base', 'order' => 8],
                ['question_text' => 'What are the three (3) parts of Transistor? (Part 2)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Collector', 'order' => 9],
                ['question_text' => 'What are the three (3) parts of Transistor? (Part 3)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Emitter', 'order' => 10],
                ['question_text' => 'What are the three (3) parts of Transformers? (Part 1)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Core', 'order' => 11],
                ['question_text' => 'What are the three (3) parts of Transformers? (Part 2)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Primary', 'order' => 12],
                ['question_text' => 'What are the three (3) parts of Transformers? (Part 3)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Secondary', 'order' => 13],
                ['question_text' => 'Give at least two (2) types of rectification process (Type 1)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Full-wave (Bridge rectifier)', 'order' => 14],
                ['question_text' => 'Give at least two (2) types of rectification process (Type 2)', 'question_type' => 'identification', 'points' => 1, 'correct_answer' => 'Half-wave (Single diode rectifier)', 'order' => 15],
            ]);

            // ── Sheet 1.4 Task Sheet ──
            $ts141 = TaskSheet::create([
                'information_sheet_id' => $sheet4->id,
                'task_number' => '1.4.1',
                'title' => 'Checking and Testing of Transistor',
                'description' => 'Hands-on activity for checking and testing transistors.',
                'instructions' => '1. Check the value of Transistor
2. Using the suggested range in testing a terminal of Transistor in finding the base, emitter and collector
3. Use the tester in both reverse and forward bias
4. Write down the parts, description (Q1,Q2,etc.), findings, the range use and the remarks if good or defective such as leaky, shorted or open.
5. Submit to Trainer for checking',
                'estimated_duration' => 30,
                'difficulty_level' => 'intermediate',
            ]);

            DB::table('task_sheet_objectives')->insert([
                ['task_sheet_id' => $ts141->id, 'objective' => 'Understand the value of transistor', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['task_sheet_id' => $ts141->id, 'objective' => 'Intensify the use of Multimeter', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['task_sheet_id' => $ts141->id, 'objective' => 'Identify the conditions of a transistor', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ]);

            // ╔══════════════════════════════════════════════════╗
            // ║  INFORMATION SHEET 1.5                          ║
            // ║  Schematic/Pictorial/Block Diagram & PCB Making ║
            // ╚══════════════════════════════════════════════════╝
            $sheet5 = InformationSheet::updateOrCreate(
                ['module_id' => $module->id, 'sheet_number' => '1.5'],
                [
                    'title' => 'Schematic Diagram, Pictorial Diagram, Block Diagram and PCB Making',
                    'content' => 'This section covers schematic diagrams, pictorial diagrams, electronic component symbols, and the process of making printed circuit boards (PCBs).',
                    'order' => 5,
                ]
            );

            $this->cleanSheetData($sheet5);

            // ── Sheet 1.5 Topics ──
            $this->createTopics($sheet5, [
                [
                    'title' => 'Schematic Diagram',
                    'order' => 1,
                    'content' => '<p>A drawing showing all significant components, parts, or tasks (and their interconnections) of a circuit, device, flow, process, or project by means of standard symbols.</p>
<p>A connection of Resistors in series and parallel is also a schematic diagram. More complex diagrams include capacitors, diodes, and other components added in series-parallel connections.</p>',
                ],
                [
                    'title' => 'Pictorial Diagram',
                    'order' => 2,
                    'content' => '<p>A simplified diagram which shows the various components of a system (motorcycle, car, ship, electronic devices, airplane, etc.) without regard to their physical location, how the wiring is marked, or how the wiring is routed. It does, however, show you the sequence in which the components are connected.</p>',
                ],
                [
                    'title' => 'Schematic and Pictorial Symbols',
                    'order' => 3,
                    'content' => '<h4>Common Component Symbols:</h4>
<table><tr><th>Component</th><th>Description</th></tr>
<tr><td>Conductors (connected)</td><td>Lines meeting at a dot</td></tr>
<tr><td>Conductors (not connected)</td><td>Lines crossing without a dot</td></tr>
<tr><td>Cell</td><td>Single cell battery symbol</td></tr>
<tr><td>Battery</td><td>Multiple cell symbol</td></tr>
<tr><td>Switch (SPST)</td><td>Single-pole Single-throw</td></tr>
<tr><td>Capacitor (Non-Polarized)</td><td>Two parallel lines</td></tr>
<tr><td>Capacitor (Polarized)</td><td>One flat, one curved line with +</td></tr>
<tr><td>Resistor</td><td>Zigzag line</td></tr>
<tr><td>Variable Resistor</td><td>Zigzag with arrow</td></tr>
<tr><td>Diode</td><td>Triangle with bar</td></tr>
<tr><td>LED</td><td>Diode symbol with arrows</td></tr>
<tr><td>Ground</td><td>Three horizontal lines decreasing in size</td></tr></table>
<h4>Schematic Diagram of Flip-Flop</h4>
<p>Components: 470R resistors (x2), 10k resistors (x2), 100u capacitors (x2), LED1 (Red), LED2 (Green), Q1 BC 547, Q2 BC 547, Switch, 9v battery</p>
<h4>Schematic Diagram of Full-Wave Bridge Type Multi Tap Transformer</h4>
<p>220V input with selector switch, multiple voltage taps (3v, 4.5v, 6v, 9v, 12v), bridge rectifier (D1-D4: 4004/06), R1-R3: 1K ohms, F: 1000uF/35volts</p>',
                ],
                [
                    'title' => 'Printed Circuit Board (PCB) Making',
                    'order' => 4,
                    'content' => '<h4>Step 1: Materials</h4>
<p>1. Copper Clad (2x2) 2. Masking Tape 3. Ruler 4. Knife Cutter 5. Pencil 6. Mini Drill 7. Ferric Chloride 8. Sand Paper 9. Plastic Container</p>
<h4>Step 2: Designing the circuit</h4>
<p>Using paper and pencil design the layout of the circuit as a top view of the board. Have all different components on hand to help with spacing and placement. Make sure the layout will fit on the board.</p>
<h4>Step 3: Drawing the traces</h4>
<p>Make a reversed copy of the design. Cut the copy and tape it onto the copper side of the PCB. Using a #65 drill bit, drill holes in the center of all solder pads for individual components.</p>
<h4>Step 4: Cutting the Excess</h4>
<p>Gently glide the knife along the edge of your desired PCB design. Strip the excess masking tape to reveal the design. Try not to puncture the design to prevent damage to connections.</p>
<h4>Step 5: Etching</h4>
<p>Find a clean dry place, preferably outside. Pour about 1/4 to 1/2" of Ferric Chloride into a small container. Fill a larger container with warm water about 1" deep. Drop PCB into the Ferric Chloride, copper side up, and place small container into the warm water. Gently rock the container. In about 5-7 minutes copper starts to dissolve. After 10-12 minutes the board should be completely etched. Remove immediately and rinse in water.</p>
<h4>Step 6: Cleaning the PCB</h4>
<p>Using 1000 grit sand paper, clean the Sharpie off the traces.</p>',
                ],
            ]);

            // ── Sheet 1.5 Self Check ──
            $sc151 = SelfCheck::create([
                'information_sheet_id' => $sheet5->id,
                'check_number' => '1.5.1',
                'title' => 'Self Check No. 1.5.1 - Schematic Diagram Interpretation',
                'description' => 'Termination of Schematic Diagram',
                'instructions' => 'Interpret the given Schematic Diagram by drawing and connecting its Pictorial Diagram.',
                'time_limit' => 30,
                'passing_score' => 70,
                'total_points' => 10,
                'is_active' => true,
            ]);

            $this->createSelfCheckQuestions($sc151, [
                ['question_text' => 'Interpret the given Schematic Diagram of a simple series circuit by drawing and connecting its Pictorial Diagram.', 'question_type' => 'essay', 'points' => 5, 'correct_answer' => 'Student should draw the pictorial equivalent of the series circuit schematic showing proper component connections.', 'order' => 1],
                ['question_text' => 'Interpret the given Schematic Diagram of a parallel circuit by drawing and connecting its Pictorial Diagram.', 'question_type' => 'essay', 'points' => 5, 'correct_answer' => 'Student should draw the pictorial equivalent of the parallel circuit schematic showing proper component connections.', 'order' => 2],
            ]);

            // ── Sheet 1.5 Task Sheets ──
            $ts151 = TaskSheet::create([
                'information_sheet_id' => $sheet5->id,
                'task_number' => '1.5.1',
                'title' => 'Drawing and Tracing of Inventory - Linear Power Supply',
                'description' => 'Draw and trace electronic components and connections of a linear power supply.',
                'instructions' => '1. Check all parts and components
2. Draw the schematic and write all values
3. Write the description, designation, qty, actual reading, range and remarks
4. Add the total value of each parts
5. Submit to Trainer for checking',
                'estimated_duration' => 45,
                'difficulty_level' => 'intermediate',
            ]);

            DB::table('task_sheet_objectives')->insert([
                ['task_sheet_id' => $ts151->id, 'objective' => 'Draw and trace electronic components and connection', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['task_sheet_id' => $ts151->id, 'objective' => 'Intensify the use of inventory / linear power supply', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['task_sheet_id' => $ts151->id, 'objective' => 'Identify the result of the inventory', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ]);

            $ts152 = TaskSheet::create([
                'information_sheet_id' => $sheet5->id,
                'task_number' => '1.5.2',
                'title' => 'Drawing and Tracing of Inventory - Flip-Flop',
                'description' => 'Checking and tracing of Flip-Flop inventory by using the multi-meter and tracing its connection for accuracy.',
                'instructions' => '1. Check all parts and components
2. Draw the schematic and write all the values of each components
3. Add the total value of the parts, describe each component, write the designation, qty, actual reading, range and remarks of it.
4. Submit to Trainer for checking',
                'estimated_duration' => 45,
                'difficulty_level' => 'intermediate',
            ]);

            DB::table('task_sheet_objectives')->insert([
                ['task_sheet_id' => $ts152->id, 'objective' => 'Check and trace Flip-Flop inventory using multi-meter', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['task_sheet_id' => $ts152->id, 'objective' => 'Trace connections for accuracy', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ]);

            // ╔══════════════════════════════════════════════════╗
            // ║  INFORMATION SHEET 1.6                          ║
            // ║  Soldering, De-soldering & Troubleshooting      ║
            // ╚══════════════════════════════════════════════════╝
            $sheet6 = InformationSheet::updateOrCreate(
                ['module_id' => $module->id, 'sheet_number' => '1.6'],
                [
                    'title' => 'Soldering and De-soldering, Terminaling and Connecting, Troubleshooting Process',
                    'content' => 'This section covers soldering techniques, tools and supplies, SMD soldering, soldering defects, and good vs bad joints.',
                    'order' => 6,
                ]
            );

            $this->cleanSheetData($sheet6);

            // ── Sheet 1.6 Topics ──
            $this->createTopics($sheet6, [
                [
                    'title' => 'Soldering Process',
                    'order' => 1,
                    'content' => '<h4>Step-by-step soldering guide:</h4>
<p>1. Get your tools ready: soldering iron, solder, wet sponge</p>
<p>2. Place the tip of the iron on the joint</p>
<p>3. Feed solder to the joint (not the iron)</p>
<p>4. Remove the solder, then the iron</p>
<p>5. Inspect the joint - it should be shiny and smooth</p>
<p>Tips: Clean the tip regularly on a wet sponge. Heat both parts of the joint. Apply solder to the joint, not the iron. Use the right amount of solder. Keep the iron tip clean and tinned.</p>',
                ],
                [
                    'title' => 'What is Soldering and Desoldering?',
                    'order' => 2,
                    'content' => '<p>Soldering is a process in which two or more items are joined together by melting and putting a filler metal (solder) into the joint, the filler metal having a lower melting point than the adjoining metal. Soldering differs from welding in that soldering does not involve melting the work pieces.</p>
<p>Soldering is a process of connecting/joining two metallic surfaces (e.g. terminals of components and the PCB copper pads) with the use of a soldering iron and a solder lead. This process is commonly used in electronics for permanent electrical connections on a Printed Circuit Board (PCB).</p>
<h4>Three types of soldering:</h4>
<p>1. Soft soldering</p>
<p>2. Hard soldering (silver soldering and brazing)</p>
<p>3. Braze welding</p>
<p>Soft soldering, which uses tin/lead alloy as its filler metal, is the most widely used for making connections in the electronics field. For electrical and electronic work, 60/40 (Sn/Pb) solder is principally used.</p>',
                ],
                [
                    'title' => 'Soldering and Desoldering Tools',
                    'order' => 3,
                    'content' => '<p><strong>A soldering iron</strong> is a hand tool used in soldering. It supplies heat to melt solder so that it can flow into the joint. For home-based electronics work, use a soldering iron in the range of 15W to 30W. A fine conical type tip is preferred for precision work.</p>
<p><strong>A soldering gun</strong> is an approximately pistol-shaped, electrically powered tool. The body contains a transformer with a primary winding connected to mains electricity through a trigger switch.</p>
<p><strong>Tweezers (Pliers)</strong> are tools used for picking up objects too small to be easily handled with human fingers.</p>
<p><strong>A Solder sucker</strong> is a device used to remove solder from a PCB. It is usually a manually operated vacuum pump, or it can be a de-solder wick (braided copper wire which uses capillary action to remove solder).</p>',
                ],
                [
                    'title' => 'Soldering Supplies and Materials',
                    'order' => 4,
                    'content' => '<p><strong>Solder</strong> is a fusible metal alloy used to create a permanent bond between metal workpieces. It must have a lower melting point than the pieces being joined and be resistant to oxidative and corrosive effects.</p>
<p><strong>Tin-lead solders</strong> (soft solders) are commercially available with tin concentrations between 5% and 70% by weight. The greater the tin concentration, the greater the tensile and shear strengths. 60/40 (Sn/Pb) solder is principally used for electrical/electronic soldering.</p>
<p><strong>Flux</strong> is a reducing agent designed to help reduce metal oxides at the points of contact. Two principal types: acid flux (for metal mending) and rosin flux (for electronics - preferred because acid flux is corrosive).</p>
<p><strong>Solder paste</strong> (or solder cream) is used to connect leads of integrated circuits in surface mount technology. Consists of flux and tiny spheres of solder.</p>
<p><strong>Solder wick</strong> (desoldering braid) is a pre-fluxed copper braid used to remove solder using capillary action.</p>',
                ],
                [
                    'title' => 'SMD Soldering',
                    'order' => 5,
                    'content' => '<p>Surface mount components are the most commonly used components in the market. SMDs are available in packages smaller than 0.4 x 0.2 mm.</p>
<p>Surface mounting is performed by a process called "reflow" soldering. A stencil with apertures is placed over the PCB, and solder paste is applied (normally by screen printing). SMDs are then placed using automated pick-and-place equipment, and the entire board is heated.</p>',
                ],
                [
                    'title' => 'Soldering Defects',
                    'order' => 6,
                    'content' => '<h4>Common defects:</h4>
<ul>
<li><strong>Cold solder joint:</strong> caused when solder cools too quickly or parts move during cooling. Has a dull, grainy appearance. Weak mechanically and a poor conductor.</li>
<li><strong>Disturbed joint:</strong> caused from touching the solder before it is set. Similar to a cold joint, but less severe.</li>
<li><strong>Overheating:</strong> caused by leaving the iron on the joint for too long, which can damage components.</li>
<li><strong>Insufficient wetting:</strong> when solder doesn\'t flow properly. Results in poor connection.</li>
<li><strong>Solder bridge:</strong> unwanted solder connecting adjacent tracks or pads on PCB.</li>
</ul>
<h4>Good Joint vs Bad Joint</h4>
<p>A good joint should be smooth, bright and shiny, without sharp projections. The solder should be bright and has the form like a small "volcano" (inverted cone).</p>
<p>A bad joint looks dull, has lumps, has air pockets or voids, or has a weak or cold solder appearance.</p>
<h4>Helpful Tips:</h4>
<p>a) Good - all surfaces wet with solder</p>
<p>b) Too little solder</p>
<p>c) Too much solder - may hide a bad joint/no connection</p>
<p>d) Pad not soldered properly - solder has not wetted the surfaces</p>
<p>e) Solder bridge - may cause a short circuit on close tracks</p>
<p>f) Solder "ball" - has not wet the surfaces, may have limited contact</p>',
                ],
            ]);

            // ── Sheet 1.6 Job Sheets ──
            $js161 = JobSheet::create([
                'information_sheet_id' => $sheet6->id,
                'job_number' => '1.6.1',
                'title' => 'Soldering - Full-Wave Bridge Type Multi Tap Transformer',
                'description' => 'Apply correct and proper technique on soldering; know and understand soldering procedures used for connecting electronic components and repair.',
                'procedures' => '1. Prepare all supplies and materials: Soldering iron, lead, flux, PCB, and passive components, Multi-tester, Magnifier, Sand Paper
2. Ask the trainer for safety usage instructions
3. Follow the schematic diagram of Full-Wave Bridge Type Multi Tap Transformer
4. Solder components according to the schematic: 220v input, selector switch, bridge rectifier D1-D4, output connections
5. Test the assembled circuit using the multi-tester
6. Submit to Trainer for checking',
                'estimated_duration' => 60,
                'difficulty_level' => 'intermediate',
            ]);

            DB::table('job_sheet_objectives')->insert([
                ['job_sheet_id' => $js161->id, 'objective' => 'Apply correct and proper technique on how to solder', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js161->id, 'objective' => 'Know and understand soldering procedures used for connecting electronic components', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js161->id, 'objective' => 'Assemble the Full-Wave Bridge Type Multi Tap Transformer', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ]);

            DB::table('job_sheet_tools')->insert([
                ['job_sheet_id' => $js161->id, 'tool_name' => 'Soldering iron', 'quantity' => '1', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js161->id, 'tool_name' => 'Multi-tester', 'quantity' => '1', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js161->id, 'tool_name' => 'Magnifier', 'quantity' => '1', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js161->id, 'tool_name' => 'Sand Paper', 'quantity' => '1', 'order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ]);

            DB::table('job_sheet_steps')->insert([
                ['job_sheet_id' => $js161->id, 'step_number' => 1, 'instruction' => 'Prepare all supplies and materials: Soldering iron, lead, flux, PCB, and passive components', 'expected_outcome' => 'All materials are ready and organized on the workbench', 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js161->id, 'step_number' => 2, 'instruction' => 'Study the schematic diagram of the Full-Wave Bridge Type Multi Tap Transformer', 'expected_outcome' => 'Student understands the component layout and connections', 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js161->id, 'step_number' => 3, 'instruction' => 'Place and solder the transformer connections on the PCB', 'expected_outcome' => 'Transformer is properly mounted with clean solder joints', 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js161->id, 'step_number' => 4, 'instruction' => 'Solder the bridge rectifier diodes (D1-D4: 4004/06)', 'expected_outcome' => 'Diodes are correctly oriented and soldered', 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js161->id, 'step_number' => 5, 'instruction' => 'Solder the resistors (R1-R3: 1K ohms) and filter capacitor (1000uF/35V)', 'expected_outcome' => 'Components are properly placed with correct values', 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js161->id, 'step_number' => 6, 'instruction' => 'Install the selector switch for voltage taps (3v, 4.5v, 6v, 9v, 12v)', 'expected_outcome' => 'Switch is properly wired to correct transformer taps', 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js161->id, 'step_number' => 7, 'instruction' => 'Test the assembled circuit using the multi-tester', 'expected_outcome' => 'Circuit produces correct DC voltage at each tap setting', 'created_at' => now(), 'updated_at' => now()],
            ]);

            $js162 = JobSheet::create([
                'information_sheet_id' => $sheet6->id,
                'job_number' => '1.6.2',
                'title' => 'Soldering - Flip-Flop Circuit',
                'description' => 'Apply correct and proper technique of soldering; should know and understand soldering procedures used for connecting electronic components and assembly.',
                'procedures' => '1. Prepare supplies and materials: Soldering iron, lead, flux, passive components
2. Prepare components: C, R, LED (red + green) x4 pcs each, Transistor x2
3. Perfboard with copper clad: 1x3
4. Tools: Multi-tester, Cutter, Long Nose, Magnifier
5. Follow schematic diagram of Flip-Flop
6. Demonstrate assembly/soldering (Plug and Play)
7. Test the assembled circuit',
                'estimated_duration' => 60,
                'difficulty_level' => 'intermediate',
            ]);

            DB::table('job_sheet_objectives')->insert([
                ['job_sheet_id' => $js162->id, 'objective' => 'Apply correct and proper soldering technique', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js162->id, 'objective' => 'Understand soldering procedures for connecting electronic components', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js162->id, 'objective' => 'Assemble the Flip-Flop circuit', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ]);

            DB::table('job_sheet_tools')->insert([
                ['job_sheet_id' => $js162->id, 'tool_name' => 'Soldering iron', 'quantity' => '1', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js162->id, 'tool_name' => 'Multi-tester', 'quantity' => '1', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js162->id, 'tool_name' => 'Cutter', 'quantity' => '1', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js162->id, 'tool_name' => 'Long Nose Pliers', 'quantity' => '1', 'order' => 4, 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js162->id, 'tool_name' => 'Magnifier', 'quantity' => '1', 'order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ]);

            DB::table('job_sheet_steps')->insert([
                ['job_sheet_id' => $js162->id, 'step_number' => 1, 'instruction' => 'Prepare all supplies: Soldering iron, lead, flux, passive components, Perfboard with copper clad (1x3)', 'expected_outcome' => 'All materials are ready and organized', 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js162->id, 'step_number' => 2, 'instruction' => 'Identify and organize components: 470R (x2), 10k (x2), 100u (x2), LED Red, LED Green, BC 547 Transistor (x2), Switch, 9V battery', 'expected_outcome' => 'All components are identified and sorted', 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js162->id, 'step_number' => 3, 'instruction' => 'Study the Flip-Flop schematic diagram and plan component placement on perfboard', 'expected_outcome' => 'Student understands the circuit layout', 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js162->id, 'step_number' => 4, 'instruction' => 'Mount and solder the transistors (Q1, Q2: BC 547) on the perfboard', 'expected_outcome' => 'Transistors are properly oriented and soldered', 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js162->id, 'step_number' => 5, 'instruction' => 'Solder the resistors (470R x2, 10k x2) and capacitors (100u x2)', 'expected_outcome' => 'Passive components are properly connected', 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js162->id, 'step_number' => 6, 'instruction' => 'Solder the LEDs (Red and Green) with correct polarity', 'expected_outcome' => 'LEDs are correctly oriented (anode/cathode)', 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js162->id, 'step_number' => 7, 'instruction' => 'Connect the switch and 9V battery holder', 'expected_outcome' => 'Power connections are secure and properly wired', 'created_at' => now(), 'updated_at' => now()],
                ['job_sheet_id' => $js162->id, 'step_number' => 8, 'instruction' => 'Test the Flip-Flop circuit - LEDs should alternate blinking', 'expected_outcome' => 'Both LEDs alternate blinking at a steady rate', 'created_at' => now(), 'updated_at' => now()],
            ]);

            DB::commit();

            $this->command->info('Module Content Seeder completed successfully!');
            $this->command->info('Created: 6 Information Sheets, Topics, Self-Checks, Task Sheets, and Job Sheets');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Module Content Seeder failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Clean existing related data for an information sheet (for idempotency).
     */
    private function cleanSheetData(InformationSheet $sheet): void
    {
        // Delete topics
        Topic::where('information_sheet_id', $sheet->id)->delete();

        // Delete self-check questions then self-checks
        $selfCheckIds = SelfCheck::where('information_sheet_id', $sheet->id)->pluck('id');
        if ($selfCheckIds->isNotEmpty()) {
            SelfCheckQuestion::whereIn('self_check_id', $selfCheckIds)->delete();
            SelfCheck::where('information_sheet_id', $sheet->id)->delete();
        }

        // Delete task sheet related data then task sheets
        $taskSheetIds = TaskSheet::where('information_sheet_id', $sheet->id)->pluck('id');
        if ($taskSheetIds->isNotEmpty()) {
            DB::table('task_sheet_objectives')->whereIn('task_sheet_id', $taskSheetIds)->delete();
            DB::table('task_sheet_materials')->whereIn('task_sheet_id', $taskSheetIds)->delete();
            DB::table('task_sheet_safety_precautions')->whereIn('task_sheet_id', $taskSheetIds)->delete();
            TaskSheet::where('information_sheet_id', $sheet->id)->forceDelete();
        }

        // Delete job sheet related data then job sheets
        $jobSheetIds = JobSheet::where('information_sheet_id', $sheet->id)->pluck('id');
        if ($jobSheetIds->isNotEmpty()) {
            DB::table('job_sheet_objectives')->whereIn('job_sheet_id', $jobSheetIds)->delete();
            DB::table('job_sheet_tools')->whereIn('job_sheet_id', $jobSheetIds)->delete();
            DB::table('job_sheet_safety_requirements')->whereIn('job_sheet_id', $jobSheetIds)->delete();
            $stepIds = DB::table('job_sheet_steps')->whereIn('job_sheet_id', $jobSheetIds)->pluck('id');
            if ($stepIds->isNotEmpty()) {
                DB::table('job_sheet_step_warnings')->whereIn('step_id', $stepIds)->delete();
                DB::table('job_sheet_step_tips')->whereIn('step_id', $stepIds)->delete();
            }
            DB::table('job_sheet_steps')->whereIn('job_sheet_id', $jobSheetIds)->delete();
            JobSheet::where('information_sheet_id', $sheet->id)->forceDelete();
        }
    }

    /**
     * Create topics for an information sheet.
     */
    private function createTopics(InformationSheet $sheet, array $topics): void
    {
        foreach ($topics as $topic) {
            Topic::create([
                'information_sheet_id' => $sheet->id,
                'title' => $topic['title'],
                'content' => $topic['content'],
                'order' => $topic['order'],
            ]);
        }
    }

    /**
     * Create self-check questions.
     */
    private function createSelfCheckQuestions(SelfCheck $selfCheck, array $questions): void
    {
        foreach ($questions as $q) {
            SelfCheckQuestion::create([
                'self_check_id' => $selfCheck->id,
                'question_text' => $q['question_text'],
                'question_type' => $q['question_type'],
                'points' => $q['points'],
                'correct_answer' => $q['correct_answer'],
                'options' => $q['options'] ?? null,
                'explanation' => $q['explanation'] ?? null,
                'order' => $q['order'],
            ]);
        }
    }
}
