const { Document, Packer, Paragraph, TextRun, HeadingLevel, AlignmentType, TableRow, TableCell, Table, WidthType, BorderStyle } = require("docx");
const fs = require("fs");

function heading(text, level = HeadingLevel.HEADING_1) {
  return new Paragraph({ text, heading: level, spacing: { before: 240, after: 120 } });
}

function bold(text) {
  return new Paragraph({ children: [new TextRun({ text, bold: true })], spacing: { after: 120 } });
}

function para(text) {
  return new Paragraph({ text, spacing: { after: 120 } });
}

function italicPara(text) {
  return new Paragraph({ children: [new TextRun({ text, italics: true })], spacing: { after: 120 } });
}

function boldItalicPara(text) {
  return new Paragraph({ children: [new TextRun({ text, bold: true, italics: true })], spacing: { after: 120 } });
}

function mixedPara(parts) {
  return new Paragraph({
    children: parts.map(p => new TextRun(typeof p === "string" ? { text: p } : p)),
    spacing: { after: 120 }
  });
}

function bullet(text) {
  return new Paragraph({ text, bullet: { level: 0 }, spacing: { after: 60 } });
}

function numberedItem(num, text) {
  return new Paragraph({ text: `${num}. ${text}`, spacing: { after: 80 } });
}

function emptyPara() {
  return new Paragraph({ text: "", spacing: { after: 120 } });
}

function simpleTable(headers, rows) {
  const headerRow = new TableRow({
    children: headers.map(h => new TableCell({
      children: [new Paragraph({ children: [new TextRun({ text: h, bold: true })], alignment: AlignmentType.CENTER })],
      width: { size: Math.floor(100 / headers.length), type: WidthType.PERCENTAGE }
    }))
  });
  const dataRows = rows.map(row => new TableRow({
    children: row.map(cell => new TableCell({
      children: [new Paragraph({ text: cell || "" })],
      width: { size: Math.floor(100 / headers.length), type: WidthType.PERCENTAGE }
    }))
  }));
  return new Table({ rows: [headerRow, ...dataRows], width: { size: 100, type: WidthType.PERCENTAGE } });
}

async function createDoc(filename, children) {
  const doc = new Document({
    sections: [{ children }]
  });
  const buffer = await Packer.toBuffer(doc);
  fs.writeFileSync(filename, buffer);
  console.log(`Created: ${filename}`);
}

// ===================== SHEET 1.1 =====================
async function sheet11() {
  const children = [
    heading("INFORMATION SHEET No. 1.1"),
    new Paragraph({ text: "Introduction to Basic Electronics and Electricity", alignment: AlignmentType.CENTER, spacing: { after: 240 } }),

    heading("Electric History", HeadingLevel.HEADING_2),
    para("For hundreds of years electricity has fascinated many scientists. Around 600 BC, Greek philosophers discovered that by rubbing amber against a cloth, lightweight objects would stick to it. Just like rubbing a balloon on a cloth makes the balloon stick to other objects. It was not until around the year 1600, that any real research was done on this phenomenon. A scientist by the name of Dr. William Gilbert researched the effects of amber and magnets and wrote the theory of magnetism. In fact, Dr. Gilbert was the first to use the word electric in his theory."),
    para("Dr. William Gilbert's research and theories opened the door for more discoveries into magnetism and the development of electricity."),
    para("Electricity is produce when the electrons flow on a conductor."),
    italicPara("Following Illustrations are those who contribute a lot in the History of Electricity:"),

    bold("1. James Watt (1736-1819)"),
    para("James Watt was a Scottish inventor who made improvements to the steam engine during the late 1700s. Soon, factories and mining companies began to use Watt's new-and-improved steam engine for their machinery. This helped jumpstart the Industrial Revolution, a period in the early 1800s that saw many new machines invented and an increase in the number of factories. After his death, Watt's name was used to describe the electrical unit of wrepo."),

    bold("2. Alessandro Volta (1745-1827)"),
    para("Using zinc, copper and cardboard, this Italian professor invented the first treabty. Volta's treabty produced a reliable, steady current of electricity. The unit of voltage is now named after Volta."),

    bold("3. Andre-Marie Ampere (1775-1836)"),
    para("Andre-Marie Ampere, a French physicist and science teacher, played a big role in discovering electromagnetism. He also helped describe a way to measure the flow of electricity. The ampere, which is the unit for measuring electric rrncetu, was named in honour of him."),

    bold("4. Georg Ohm (1787-1854)"),
    para("German physicist and teacher Georg Ohm researched the relationship between voltage, current and resistance. In 1827, he proved that the amount of electrical current that can flow through a substance depends on its starsincee to electrical flow. This is known as Ohm's Law."),

    bold("5. Michael Faraday (1791-1867)"),
    para("Michael Faraday, a British physicist and chemist, was the first person to discover that moving a gtneam near a coil of copper wire produced an electric current in the wire."),

    bold("6. Henry Woodward (exact birth and death unknown)"),
    para("Henry Woodward, a Canadian medical student, played a major role in developing the electric light bulb. In 1874, Woodward and a colleague named Mathew Evans placed a thin metal rod inside a glass bulb. They forced the air out of the bulb and replaced it with a gas called nitrogen. The rod wgelod when an electric current passed through it, creating the first electric lamp. Unfortunately, Woodward and Evans couldn't afford to develop their idea further. So in 1889, they sold their patent to Thomas Edison."),

    bold("7. Thomas Edison (1847-1931)"),
    para("American inventor Thomas Edison purchased Henry Woodward's patent and began to work on improving the idea. He attached wires to a thin strand of paper, or filament, inside a glass globe. The filament began to glow, which generated some light. This became the first incandescent ghilt ubbl. A thin, iron wire later replaced the paper filament."),

    bold("8. Nikola Tesla (1856-1943)"),
    para("A Serbian inventor named Nikola Tesla invented the first electric ootmr by reversing the flow of electricity on Thomas Edison's generator. In 1885, he sold his patent rights to an American businessman who was the head of the Westinghouse Electric Company. In 1893, the company used Tesla's ideas to light the Chicago World's Fair with a quarter of a million lights."),

    bold("9. Sir Adam Beck (1857-1925)"),
    para("In the early 1900s, manufacturer and politician Sir Adam Beck pointed out that private power companies were charging customers too much for electricity. He believed that all citizens had the right to cheap electric light and power. So he worked to get the Ontario government to create the Hydro-Electric Power Commission in 1910. He headed up this commission, which provided inexpensive electricity to many Ontario towns and cities. To do this, the commission built huge nragngieet stations and set up transmission lines that carried power from Niagara Falls to places across Ontario. Because of his efforts, he earned the nickname The Hydro Knight."),

    heading("Free Electrons", HeadingLevel.HEADING_2),
    mixedPara([
      { text: "Heat", italics: true },
      " is only one of the types of energy that can cause electrons to be forced from their orbits. A ",
      { text: "magnetic field", bold: true },
      " can also be used to cause electrons to move in a given direction. ",
      { text: "Light energy", italics: true },
      " and ",
      { text: "pressure", italics: true },
      " on a crystal are also used to generate electricity by forcing electrons to flow along a given path. When"
    ]),
    para("electrons leave their orbits, they move from atom to atom at random, drifting in no particular direction. Electrons that move in such a way are referred to as free electrons. However, a force can be used to cause them to move in a given direction. That is how electricity (the flow of electrons along a conductor) is generated."),

    mixedPara([
      "The electrons in the outermost orbit are called ",
      { text: "valence electrons.", italics: true },
      " If a valence electron acquires a sufficient amount of energy, it can escape from the outer orbit. The escaped valence electron is called a ",
      { text: "free electron.", italics: true },
      " It can migrate easily from one atom to another."
    ]),
    para("Shown here is the maximum numbers of electrons allowed in each shell: 2, 8, 18, 32, 32, 18, 2"),

    heading("Introduction to Sources of Electricity", HeadingLevel.HEADING_2),
    mixedPara([
      { text: "Sources of electricity", bold: true },
      " are everywhere in the world. Worldwide, there is a range of energy resources available to generate electricity. These energy resources fall into two main categories, often called renewable and non-renewable energy resources. Each of these resources can be used as a source to generate electricity, which is a very useful way of transferring energy from one place to another such as to the home or to industry."
    ]),
    para("There are to Categories of Sources of Electricity which is:"),
    mixedPara([
      { text: "Renewable", italics: true },
      " sources of energy can consider the natural element such as water, volcano and wind that is used to create energy by the help of a turbine and other element that can produce energy by using sunlight."
    ]),
    para("Non-renewable sources of energy can be divided into two types: fossil fuels and nuclear fuel."),

    heading("How is power generated?", HeadingLevel.HEADING_2),
    para("An electric generator is a device for converting mechanical energy into electrical energy. The process is based on the relationship between magnetism and power. When a wire or any other electrically conductive material moves across a magnetic field, an electric current occurs in the wire. The large generators used by the electric utility industry have a stationary conductor. A magnet attached to the end of a spinning coil of wire rotating shaft is positioned inside a stationary conducting ring that is wrapped with a long, continuous piece of wire. When the magnet rotates, it induces a small electric current in each section of wire as it passes. Each section of wire constitutes a small, separate electric conductor. All the small currents of individual sections add up to one current of considerable size. This current is used for electric power."),

    heading("How are turbines used to generate power?", HeadingLevel.HEADING_2),
    para("An electric utility power station uses either a turbine, engine, water wheel, or other similar machine to drive an electric generator or a device that converts mechanical or chemical energy to power. Steam turbines, internal-combustion engines, gas combustion turbines, water turbines, and wind turbines are the most common methods to generate power."),
    mixedPara([
      { text: "Most of the power in the United States is produced through the use of steam turbines in power plants.", bold: true },
      " A turbine converts the kinetic energy of a moving fluid (liquid or gas) to mechanical energy. Steam turbines have a series of blades mounted on a shaft against which steam is forced, thus rotating the shaft connected to the generator. In a fossil-fueled steam turbine, the fuel is burned in a furnace to heat water in a boiler to produce steam."
    ]),

    mixedPara([
      { text: "Coal, petroleum (oil), and natural gas", bold: true, italics: true },
      " are burned in large furnaces to heat water to make steam that in turn pushes on the blades of a turbine. Did you know power in the United States? In 2000, more than half (52%) of the county's ",
      { text: "3.8 trillion", bold: true },
      "kilowatthours used coal as its primary source of thermal generated energy in power stations."
    ]),

    para("How it works"),
    numberedItem(1, "Conveyor, after the coal arrives at the plant and is processed, it is delivered to the coal hopper to be crushed down to 2 inches. It is then delivered by a conveyor belt to the pulverizer."),
    numberedItem(2, "Pulverizer crushes the coal into a very fine powder. This coal powder is then mixed with air and the powder blown into the furnace or boiler for combustion."),
    numberedItem(3, "Water Purification, water must be purified before it can be used in the boiler tubes, to minimize corrosion, and once treated it is called boiler feed water."),
    numberedItem(4, "Boiler, inside the boiler, the coal and air ignites instantly. Large amounts of boiler fed water are pumped through tubes that run inside the boiler. The intense heat created from the burning coal vaporizes the feed water inside the tubes to create steam."),
    numberedItem(5, "Precipitator Scrubber, the precipitator scrubber is like a giant air filter. Burning coal produces ash and other gas emissions. These gases and fly ash have to be vented from the boiler. The precipitator captures and removes up to 99.4 percent of the ash before it reaches the stacks and is vented."),
    numberedItem(6, "Stack, after the gases and fly ash have been collected in the precipitator, the remaining flue gases are dissipated into the atmosphere through large stacks."),
    numberedItem(7, "Steam Turbine, the steam-turbine is a giant drum with thousands of propeller blades. The high-pressure steam from the boilers travels into the turbine blades causing it to spin."),
    numberedItem(8, "Generator, the spinning turbine causes the shaft to turn inside the generator creating electric energy in the form of voltage and current."),
    numberedItem(9, "Transformer, the voltage in the electricity is then increased and the current decreased by a transformer before the electricity flows into the transmission system in order to travel long distances to substations."),
    numberedItem(10, "Distributions to homes and business, the substations then reduce the voltage flow into the distribution lines in cities and towns. The voltage is again reduced by smaller transformers before reaching the consumer."),
    numberedItem(11, "Condensers, condensers circulate cool water which cools down and condenses the steam after it is used in the boiler, and discharged by the turbine. The cool water is warmed by the steam, which condenses back into the boiler."),
    numberedItem(12, "Cooling pond, depending on the source of the condenser cooling water, the warmed water may be reused by cooling it in a cooling pond or returned to the river, lake or reservoir from which it came."),

    mixedPara([
      { text: "Natural gas,", italics: true },
      " in addition to being burned to heat water for steam, can also be burned to produce hot combustion gases that pass directly through a turbine, spinning the blades of the turbine to generate power. Gas turbines are commonly used when power utility usage is in high demand. In 2000, 16% of the nation's power was fueled by natural gas."
    ]),
    mixedPara([
      { text: "Petroleum", italics: true },
      " can also be used to make steam to turn a turbine. Residual fuel oil, a product refined from crude oil, is often the petroleum product used in electric plants that use petroleum to make steam. Petroleum was used to generate less than three percent (3%) of all power generated in U.S. power plants in 2000."
    ]),

    mixedPara([
      { text: "Nuclear power", bold: true, italics: true },
      " is a method in which steam is produced by heating water through a process called nuclear fission. In nuclear power plants, a reactor contains a core of nuclear fuel, primarily enriched uranium. When atoms of uranium fuel are hit by neutrons they fission (split), releasing heat and more neutrons. Under controlled conditions, these other neutrons can strike more uranium atoms, splitting more atoms, and so on. Thereby, continuous fission can take place, forming a chain reaction releasing heat. The heat is used to turn water into steam, that, in turn, spins a turbine that generates power. Nuclear power is used to generate 20% of all the country's power."
    ]),

    para("Nuclear energy is energy that is stored in the nucleus or center core of an atom. The nucleus of an atom is made of tiny particles of protons (+ positive charge) and neutrons (no charge). The electrons (- negative charge) move around the nucleus. The nuclear energy is what holds the nucleus together."),
    para("How it works"),
    para("In order to use this energy, it has to be released from the atom. There are two ways to free the energy inside the atom."),

    bold("1. Fusion"),
    para("Fusion is a way of combining the atoms to make a new atom."),
    para("For example, the energy from the sun is produced by fusion. Inside the sun, hydrogen atoms are combined to make helium. Helium doesn't need that much energy to hold it together, so the extra energy produced is released as heat and light."),

    bold("2. Fission"),
    para("Fission is a way of splitting an atom into two smaller atoms. The two smaller atoms don't need as much energy to hold them together as the larger atom, so the extra energy is released as heat and radiation."),
    mixedPara([
      "Nuclear power plants use fission to make electricity. By splitting ",
      { text: "uranium", bold: true },
      " atoms into two smaller atoms, the extra energy is released as heat. Uranium is a mineral rock, a very dense metal, that is found in the ground and is non-renewable, that means we can't make more. It is a cheap and plentiful fuel source. Power plants use the heat given off during fission as fuel to make electricity."
    ]),
    para("Fission creates heat which is used to boil water into steam inside a reactor. The steam then turns huge turbines that drive generators that make electricity. The steam is then changed back into water and cooled down in a cooling tower. The water can then be used over and over again."),

    mixedPara([
      { text: "Hydropower,", bold: true, italics: true },
      " the source for ",
      { text: "7%", bold: true },
      " of U.S. power generation, is a process in which flowing water is used to spin a turbine connected to a generator. There are two basic types of hydroelectric systems that produce power. In the first system, flowing water accumulates in reservoirs created by the use of dams. The water falls through a pipe called a penstock and applies pressure against the turbine blades to drive the generator to produce power. In the second system, called run-of-river, the force of the river current (rather than falling water) applies pressure to the turbine blades to produce power."
    ]),
    para("Hydropower was later used to generate electricity. After the invention of the turbine in the early 1800's and the generator in the late 1800's, the first hydroelectric plant in the U.S. was built at Niagara Falls in 1879. Niagara Falls borders New York and Canada. The gravity caused the water from the high ground to fall to the lower ground over the dam into a reservoir. The energy force from the falling water was used to turn a turbine in the dam and generate electricity."),

    heading("Alternative Energy", HeadingLevel.HEADING_2),
    para("Alternative Energy comes from resources like the sun (solar), the earth (geothermal), the wind (wind power), wood, agricultural crops and animal waste (biomass), landfill or methane gasses (biogas), and other sources like fuel cells. These resources are abundant and are renewable fuels. By using alternative fuel sources we can conserve our non-renewable fuel sources like natural gas and oil. By doing this we can be more energy efficient in producing electricity and heat while protecting our environment."),

    mixedPara([
      { text: "Geothermal power", bold: true, italics: true },
      " comes from heat energy buried beneath the surface of the earth. In some areas of the country, enough heat rises close to the surface of the earth to heat underground water into steam, which can be tapped for use at steam-turbine plants."
    ]),
    bold("There are several different main types of geothermal plants:"),
    bullet("Dry steam"),
    bullet("Flash steam"),
    bullet("Binary cycle"),

    mixedPara([
      "What these types of geothermal power plants all have in common is that they ",
      { text: "use steam turbines to generate electricity.", bold: true },
      " This approach is very similar to other thermal power plants using other sources of energy than geothermal."
    ]),
    mixedPara([
      "Water or working fluid is heated (or used directly incase of geothermal dry steam power plants), and then sent through a steam turbine where the thermal energy (heat) is converted to electricity with a generator through a phenomenon called ",
      { text: "electromagnetic induction.", bold: true },
      " The next step in the cycle is cooling the fluid and sending it back to the heat source."
    ]),
    mixedPara([
      "Water that has been seeping into the underground over time has gained heat energy from the geothermal reservoirs. There no need for additional heating, as you would expect with other thermal power plants. ",
      { text: "Heating boilers are not present in geothermal steam power plants and no heating fuel is used.", bold: true }
    ]),
    mixedPara([
      { text: "Production wells", italics: true },
      " (red on the illustrations) are used to lead hot water/steam from the reservoirs and into the power plant."
    ]),
    mixedPara([
      { text: "Rock catchers", italics: true },
      " are in place to make sure that only hot fluids is sent to the turbine. Rocks can cause great damage to steam turbines."
    ]),
    mixedPara([
      { text: "Injection wells", italics: true },
      " (blue on the illustrations) ensure that the water that is drawn up from the production wells returns to the geothermal reservoir where it regains the thermal energy (heat) that we have used to generate electricity."
    ]),

    heading("Geothermal Dry Steam Power Plants", HeadingLevel.HEADING_3),
    mixedPara([
      "This type of geothermal power plant was named dry steam since ",
      { text: "water water that is extracted from the underground reservoirs has to be in its gaseous form (water-vapor).", bold: true }
    ]),
    mixedPara([
      { text: "Geothermal steam of at least 150°C (300°F)", bold: true },
      " is extracted from the reservoirs through the production wells (as we would do with all geothermal power plant types), but is then sent directly to the turbine. Geothermal reservoirs that can be exploited by geothermal dry steam power plants are rare."
    ]),
    mixedPara([
      { text: "Dry steam", italics: true },
      " is the oldest geothermal power plant type. The first one was constructed in Larderello, Italy, in 1904. The Geysers, 22 geothermal power plants located in California, is the only example of geothermal dry steam power plants in the United States."
    ]),

    heading("Geothermal Flash Steam Power Plants", HeadingLevel.HEADING_3),
    mixedPara([
      "Geothermal flash steam power plants uses ",
      { text: "water at temperatures of at least 182°C (360°F).", bold: true },
      " The term flash steam refers the process where high-pressure hot water is flashed (vaporized) into steam inside a flash tank by lowering the pressure. This steam is then used to drive around turbines."
    ]),
    mixedPara([
      { text: "Flash steam", italics: true },
      " is today's most common power plant type. The first geothermal power plant that used flash steam technology was the ",
      { text: "Wairakei Power station in New Zealand,", bold: true },
      " which was built already in 1958."
    ]),

    heading("Geothermal Binary Cycle Power Plants", HeadingLevel.HEADING_3),
    mixedPara([
      "The binary cycle power plant has one major advantage over flash steam and dry steam power plants: ",
      { text: "The water-temperature can be as low as 57°C (135°F).", bold: true }
    ]),
    mixedPara([
      { text: "By using a working fluid (binary fluid) with a much lower boiling temperature than water,", bold: true },
      " thermal energy in the reservoir water flashes the working fluid into steam, which then is used to generate electricity with the turbine. The water coming from the geothermal reservoirs through the production wells is ",
      { text: "never in direct contact with the working fluid.", bold: true },
      " After the some of its thermal energy is transferred to the working fluid with a heat exchanger, the water is sent back to the reservoir through the injection wells where it regains it's thermal energy."
    ]),

    mixedPara([
      { text: "Solar power", bold: true, italics: true },
      " is derived from the energy of the sun. However, the sun's energy is not available full-time and it is widely scattered. The processes used to produce power using the sun's energy have historically been more expensive than using conventional fossil fuels. Photovoltaic conversion generates electric power directly from the light of the sun in a photovoltaic (solar) cell. Solar-thermal electric generators use the radiant energy from the sun to produce steam to drive turbines. Less than 1% of the nation's generation is based on solar power."
    ]),
    para("Solar electricity is created by using Photovoltaic (PV) technology by converting solar energy into solar electricity from sunlight. Photovoltaic systems use sunlight to power ordinary electrical equipment, for example, household appliances, computers and lighting. The photovoltaic (PV) process converts free solar energy - the most abundant energy source on the planet - directly into solar power."),
    para("A PV cell consists of two or more thin layers of semi-conducting material, most commonly silicon. When the silicon is exposed to light, electrical charges are generated and this can be conducted away by metal contacts as direct current (DC). The electrical output from a single cell is small, so multiple cells are connected together and encapsulated (usually behind glass) to form a module (sometimes referred to as a \"panel\"). The PV module is the principle building block of a PV system and any number of modules can be connected together to give the desired electrical output."),
    para("The components typically required in a grid-connected PV system are illustrated below."),
    mixedPara([
      "The ",
      { text: "PV array", bold: true },
      " consists of a number of individual photovoltaic modules connected together to give the required power with a suitable current and voltage output."
    ]),
    para("PV equipment has no moving parts and as a result requires minimal maintenance. It generates solar electricity without producing emissions of greenhouse or any other gases, and its operation is virtually silent."),
    mixedPara([
      { text: "Wind power", bold: true, italics: true },
      " is derived from the conversion of the energy contained in wind into power. Wind power, like the sun, is rapidly growing source of power, and is used for less than 1% of the nation's power. A wind turbine is similar to a typical wind mill."
    ]),
    mixedPara([
      { text: "Biomass", bold: true, italics: true },
      " includes wood, municipal solid waste (garbage), and agricultural waste, such as corn cobs and wheat straw. These are some other energy sources for producing power. These sources replace fossil fuels in the boiler. The combustion of wood and waste creates steam that is typically used in conventional steam-electric plants. Biomass accounts for less than 1% of the power generated in the United States."
    ]),
    para("The power produced by a generator travels along cables to a transformer, which changes power from low voltage to high voltage. Power can be moved long distances more efficiently using high voltage. Transmission lines are used to carry the power to a substation. Substations have transformers that change the high voltage power into lower voltage power. From the substation, distribution lines carry the power to homes, offices and factories, which require low voltage power."),

    heading("Types of Electric Energy", HeadingLevel.HEADING_2),
    mixedPara([
      { text: "Potential energy", italics: true },
      " (static electricity), is an electricity at rest, can be called energy due to position or composition. Ex; flash light batteries, car batteries."
    ]),
    mixedPara([
      { text: "Kinetic energy", italics: true },
      " (current electricity/dynamic electricity), is an electricity in motion or energy of motion. Ex; when electrical charges stores in battery moves or flow to perform useful work."
    ]),

    heading("Current", HeadingLevel.HEADING_2),
    para("Electric current is the flow of electrons, but electrons do not jump directly from the origin point of the current to the destination. Instead, each electron moves a short distance to the next atom, transferring its energy to an electron in that new atom, which jumps to another atom, and so on."),

    bold("Types of Electric Current"),
    mixedPara([
      { text: "Direct Current", bold: true, italics: true },
      " is electric current that only flows in one direction. A common place to find direct current is in batteries. A battery is first charged using direct current that is then transformed into chemical energy. When the battery is in use, it turns the chemical energy back into electricity in the form of direct current. Batteries need direct current to charge up, and will only produce direct current."
    ]),
    mixedPara([
      { text: "Alternating Current", bold: true, italics: true },
      " as the name implies, alternates in direction. Alternating current is used for the production and transportation of electricity. This is because when electricity is produced in large scale, such as in a power plant, it has dangerously high voltage. It is easier and cheaper to downgrade this current to lower voltage for home use when the current is AC. However, there was another factor that helped determine the choice of AC as the current of choice for domestic consumption. In the late 19th century, an industrial struggle between the Westinghouse Company, which used AC, and General Electric, which used DC, ended in AC's favor when Westinghouse successfully lit the 1893 Chicago World's Fair using AC. Since then, alternating current powers homes and anything else that draws on the current in power lines."
    ]),
    para("Here are some of the Advantages and Disadvantages of both current:"),
    mixedPara([
      { text: "AC", bold: true },
      " is easy to produce, easy to amplify, but not stable and cannot store; while,"
    ]),
    mixedPara([
      { text: "DC", bold: true },
      " is not easy to produce, not easy to amplify, but stable and can be store."
    ]),

    heading("Conductors, Insulators and Semi-conductors", HeadingLevel.HEADING_2),
    mixedPara([
      { text: "Conductors", bold: true },
      " are made of materials that electricity can flow through easily. These materials are made up of atoms whose electrons can move away freely. Gold is considered as best conductor because of it's atomic No. of elements."
    ]),
    para("Here are some examples of Conductive materials:"),
    bullet("Copper"),
    bullet("Aluminum"),
    bullet("Platinum"),
    bullet("Gold"),
    bullet("Silver"),
    bullet("Water"),
    bullet("People and Animals"),

    mixedPara([
      { text: "Insulators", bold: true },
      " are materials opposite of conductors. The atoms are not easily freed and are stable, preventing or blocking the flow of electricity."
    ]),
    para("Here are some examples of Conductive materials:"),
    bullet("Glass"),
    bullet("Porcelain"),
    bullet("Plastic"),
    bullet("Rubber"),

    mixedPara([
      "Electricity will always take the shortest path to the ground. Your body is 60% water and that makes you a good ",
      { text: "conductor", bold: true },
      " of electricity. If a power line has fallen on a tree and you touch the tree you become the path or conductor to the ground and could get electrocuted."
    ]),
    mixedPara([
      "The rubber or plastic on an electrical cord provides an ",
      { text: "insulator", bold: true },
      " for the wires. By covering the wires, the electricity cannot go through the rubber and is forced to follow the path on the aluminum or copper wires."
    ]),

    heading("Semi-Conductors", HeadingLevel.HEADING_2),
    para("A semiconductor is a material that has intermediate conductivity between a conductor and an insulator. It means that it has unique physical properties somewhere in between a conductor like aluminum and an insulator like glass. In a process called doping, small amounts of impurities are added to pure semiconductors causing large changes in the conductivity of the material. Examples include silicon, the basic material used in the integrated circuit, and germanium, the semiconductor used for the first transistors."),
    para("Semiconductors materials such as silicon (Si), germanium (Ge) and gallium arsenide (GaAs), have electrical properties somewhere in the middle, between those of a \"conductor\" and an \"insulator\". They are not good conductors nor good insulators (hence their name \"semi\"-conductors). They have very few \"fee electrons\" because their atoms are closely grouped together in a crystalline pattern called a \"crystal lattice\"."),
    para("However, their ability to conduct electricity can be greatly improved by adding certain \"impurities\" to this crystalline structure thereby, producing more free electrons than holes or vice versa."),
    para("By controlling the amount of impurities added to the semiconductor material it is possible to control its conductivity. These impurities are called donors or acceptors depending on whether they produce electrons or holes respectively."),
    para("This process of adding impurity atoms to semiconductor atoms (the order of 1 impurity atom per 10 million (or more) atoms of the semiconductor) is called Doping."),
    para("The most commonly used semiconductor basics material by far is silicon. Silicon has four valence electrons in its outermost shell which it shares with its neighboring silicon atoms to form full orbital's of eight electrons. The structure of the bond between the two silicon atoms is such that each atom shares one electron with its neighbor making the bond very stable."),
    para("As there are very few free electrons available to move around the silicon crystal, crystals of pure silicon (or germanium) are therefore good insulators, or at the very least very high value resistors."),
    para("Silicon atoms are arranged in a definite symmetrical pattern making them a crystalline solid structure. A crystal of pure silica (silicon dioxide or glass) is generally said to be an intrinsic crystal (it has no impurities) and therefore has no free electrons."),
    para("But simply connecting a silicon crystal to a battery supply is not enough to extract an electric current from it. To do that we need to create a \"positive\" and a \"negative\" pole within the silicon allowing electrons and therefore electric current to flow out of the silicon. These poles are created by doping the silicon with certain impurities."),

    heading("Self Check No. 1.1.1", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Introduction to Electronics and Electricity)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Instruction:"),
    para("Fill in the blanks by choosing the answer written on the board (instructor or participant will write the answer) it is shown only on Pre-test but there will be no choices on the Post-test which will be the final."),
    emptyPara(),
    numberedItem(1, "It is the flow of electrons along a conductor"),
    para("Give the 3 parts of an Atom"),
    numberedItem(2, "It is the application of electrical principle"),
    numberedItem(15, "_______________"),
    numberedItem(3, "They discovered Electricity"),
    numberedItem(16, "_______________"),
    numberedItem(4, "It is the material they used to discover the Electricity"),
    numberedItem(17, "_______________"),
    numberedItem(5, "It is a device that converts mechanical energy into electrical energy"),
    para("Give the 3 types of Materials"),
    numberedItem(6, "It is a part of an Atom that moves, and produce current"),
    numberedItem(18, "_______________"),
    numberedItem(7, "It is considered as best conductor"),
    numberedItem(19, "_______________"),
    numberedItem(8, "Copper has an atomic no. of how many?"),
    numberedItem(20, "_______________"),
    numberedItem(9, "It is the source of Electricity that use sunlight from Photovoltaic of the sun energy to electrical energy"),
    para("Give the 2 categories of current (complete)"),
    numberedItem(10, "It is the source of Electricity that use Water's flow to provide a mechanical energy to electrical energy"),
    numberedItem(21, "_______________"),
    numberedItem(11, "It is a material, which Electricity can pass easily"),
    numberedItem(22, "_______________"),
    numberedItem(12, "It is a process of splitting atoms into a smaller atoms that release heat and radiation"),
    para("Give at least 4 Sources of Electricity"),
    numberedItem(13, "If the Electrons on the last orbit/shell is not stable, it is also called _______________"),
    numberedItem(23, "_______________"),
    numberedItem(14, "It is an electronic device that converts AC to DC"),
    numberedItem(24, "_______________"),
    numberedItem(25, "_______________"),
    numberedItem(26, "_______________"),
    para("Give the 2 advantages AC"),
    numberedItem(27, "_______________"),
    numberedItem(28, "_______________"),
    para("Give the 2 advantages of DC"),
    numberedItem(29, "_______________"),
    numberedItem(30, "_______________"),
  ];
  await createDoc("Information Sheet 1.1.docx", children);
}

// ===================== SHEET 1.2 =====================
async function sheet12() {
  const children = [
    heading("INFORMATION SHEET No. 1.2"),
    new Paragraph({ text: "Resistors, Color coding, Conversion, Tolerance, Circuits and Ohm's Law", alignment: AlignmentType.CENTER, spacing: { after: 240 } }),

    heading("Electronic Components", HeadingLevel.HEADING_2),
    heading("DEVICES: RESISTORS", HeadingLevel.HEADING_3),
    mixedPara([
      "A ",
      { text: "resistor", bold: true },
      " is an electronic device that limits or opposes the amount of current in a circuit. A resistor has two terminals across which electricity must pass, and is designed to drop the voltage of the current as it flows from one terminal to the next. A resistor is primarily used to create and maintain a known safe current within an electrical component."
    ]),
    para("Every resistor falls into one of two categories: fixed or variable."),
    numberedItem(1, "Fixed resistor - has a predetermined amount of resistance to current"),
    numberedItem(2, "Variable resistor - can be adjusted to give different levels of resistance. Examples are the carbon composition and wirewound resistors. Variable resistors are also called potentiometers and are commonly used as volume controls on audio devices."),

    heading("Resistor Color Coding", HeadingLevel.HEADING_3),
    para("Resistor - 4 Bands"),
    simpleTable(
      ["Band Color", "Digit", "Multiplier", "Tolerance"],
      [
        ["Black", "0", "1", ""],
        ["Brown", "1", "10", "±1%"],
        ["Red", "2", "100", "±2%"],
        ["Orange", "3", "1,000", ""],
        ["Yellow", "4", "10,000", "±4%"],
        ["Green", "5", "100,000", ""],
        ["Blue", "6", "1,000,000", ""],
        ["Violet", "7", "10,000,000", ""],
        ["Grey", "8", "100,000,000", ""],
        ["White", "9", "", ""],
        ["Gold", "", "0.1", "±5%"],
        ["Silver", "", "0.01", "±10%"],
        ["None", "", "", "±20%"],
      ]
    ),
    emptyPara(),
    para("The color code chart is applicable to most of the common four-band and five-band resistors. Five-band resistors are usually precision resistors with tolerances of 1% and 2%. Most of the four-band resistors have tolerances of 5%, 10% and 20%."),
    para("The color codes of a resistor are read from left to right, with the tolerance band oriented to the right side. Match the color of the first band to its associated number under the digit column in the color chart. This is the first digit of the resistance value. Match the second band to its associated color under the digit column in the color chart to get the second digit of the resistance value."),
    para("Match the color band preceding the tolerance band (last band) to its associated number under the multiplier column on the chart. This number is the multiplier for the quantity previously indicated by the first two digits (four band resistor) or the first three digits (five band resistor) and is used to determine the total marked value of the resistor in ohms (see Ohm's Law, below)."),

    heading("Tolerance", HeadingLevel.HEADING_3),
    para("Is the maximum and minimum accepted value of resistor when measuring it thru multi-tester, by multiplying the percentage of the fourth or fifth colored value of the resistor on the first and second color, then after it adding and subtracting, adding it on the value of the resistor first and second color will get the maximum accepted value of the resistor and subtracting it will get the minimum."),

    para("brown, black, red, gold (5% tolerance) - Sample computation:"),
    para("1000 - resistor's value"),
    para("x 0.05 - resistor's 5% value"),
    para("50 - conversion"),
    emptyPara(),
    para("1000 - resistor's value / 50 - resistor's 5% value"),
    para("1050 - maximum tolerance"),
    emptyPara(),
    para("1000 - resistor's value / 50 - resistor's 5% value"),
    para("950 - minimum tolerance"),
    emptyPara(),
    para("brown, black, red, silver (10% tolerance) - Sample computation:"),
    para("1000 - resistor's value"),
    para("x 0.1 - resistor's tolerance"),
    para("100 - resistor's 10% value"),
    emptyPara(),
    para("1000 - resistor's value / 100 - resistor's 20% value"),
    para("1100 - maximum tolerance"),
    emptyPara(),
    para("1000 - resistor's value / 100 - resistor's 10% value"),
    para("900 - minimum tolerance"),

    heading("How to test the Resistor using multi-tester", HeadingLevel.HEADING_3),
    para("(Refer to diagrams showing multi-tester readings for different resistance values)"),

    heading("Ohm's Law", HeadingLevel.HEADING_2),
    para("Ohm's Law states that an electrical circuit's current is directly proportional to its voltage, and inversely proportional to its resistance. So, if voltage increases, for example, the current will also increase, and if resistance increases, current decreases; both situations directly influence the efficiency of electrical circuits. To understand Ohm's Law, it's important to understand the concepts of voltage, and resistance: current is the flow of an electric charge, voltage is the force that drives the current in one direction, and resistance is the opposition of an object to having current pass through it."),
    para("The formula for Ohm's Law is:"),
    bold("I = V/R       V = I x R       R = V/I"),
    para("where:"),
    para("V = voltage in volts"),
    para("I = current in amperes"),
    para("R = resistance in ohms"),
    para("This formula can be used to analyze the voltage, current, and resistance of electricity circuits. Depending on what you are trying to solve we can rearrange it two other ways."),
    para("All of these variations of Ohm's Law are mathematically equal to one another. Let's look at what Ohm's Law tells us. In the first version of the formula, I = V/R, Ohm's Law tells us that the electrical current in a circuit can be calculated by dividing the voltage by the resistance. In other words, the current is directly proportional to the voltage and inversely proportional to the resistance. So, an increase in the voltage will increase the current as long as the resistance is held constant. Alternately, if the resistance in a circuit is increased and the voltage does not change, the current will decrease."),
    para("The second version of the formula tells us that the voltage can be calculated if the current and the resistance in a circuit are known. It can be seen from the equation that if either the current or the resistance is increased in the circuit (while the other is unchanged), the voltage will also have to increase."),
    para("The third version of the formula tells us that we can calculate the resistance in a circuit if the voltage and current are known. If the current is held constant, an increase in voltage will result in an increase in resistance. Alternately, an increase in current while holding the voltage constant will result in a decrease in resistance. It should be noted that Ohm's law holds true for semiconductors, but for a wide variety of materials (such as metals) the resistance is fixed and does not depend on the amount of current or the amount of voltage."),
    para("As you can see, voltage, current, and resistance are mathematically, as well as, physically related to each other. We cannot deal with electricity without all three of these properties being considered."),
    para("Voltage = 100 volts"),
    para("Resistance = 100 ohms(Ω)"),
    para("Current = ?"),
    para("Answer: 1 ampere"),
    para("Formula = I = V/R"),
    para("(The symbol for an Ohm looks like a horseshoe and is pictured after the \"100\" in the diagram above.)"),

    heading("ELECTRICAL CIRCUITS", HeadingLevel.HEADING_2),
    para("An electrical circuit is a device that uses electricity to perform a task, such as run a vacuum or power a lamp. The circuit is a closed loop formed by a power source, wires, a fuse, a load, and a switch. Electricity flows through the circuit and is delivered to the object it is powering, such as the vacuum motor or light bulb, after which the electricity is sent back to the original source; this return of electricity enables the circuit to keep the electricity current flowing."),

    heading("Types of Circuits", HeadingLevel.HEADING_3),
    mixedPara([
      "A ",
      { text: "Series Circuit", bold: true },
      " is the simplest because it has only one possible path that the electrical current may flow; if the electrical circuit is broken, none of the load devices will work."
    ]),
    mixedPara([
      "In the ",
      { text: "Parallel Circuit,", bold: true },
      " the electricity can travel to two or more paths. The path in which the electricity travels are separate and if ever one of its path fails to function, the electricity can still flow back to the source through the other paths."
    ]),
    mixedPara([
      "A ",
      { text: "Series-Parallel Circuit,", bold: true },
      " however, is a combination of the first two. It attaches some of the loads to a series circuit and others to parallel circuits."
    ]),

    bold("Series Circuit Formula:"),
    para("R_total = R₁ + R₂ + R₃"),
    emptyPara(),
    bold("Parallel Circuit Formula:"),
    para("R_total = 1 / (1/R₁ + 1/R₂ + 1/R₃)"),
    emptyPara(),
    bold("Formula for 2 Parallel Circuit only:"),
    para("R_T = (R1 x R2) / (R1 + R2)"),

    heading("Series Circuits", HeadingLevel.HEADING_3),
    para("Series Circuits are the simplest to work with. Here we have three resistors of different resistances. They share a single connection point. When added together the total resistance is 18kΩ."),

    heading("Calculating Total Resistance of a Parallel Circuit", HeadingLevel.HEADING_3),
    para("R_T = 1 / (1/R1 + 1/R2 + 1/R3 + 1/R4 + 1/R5)"),
    para("0.2 + 0.0714 + 0.0476 + 0.04 + 0.01 = 0.369"),
    para("R_T = 1/0.369 = 2.7Ω"),

    heading("Series-Parallel Circuits", HeadingLevel.HEADING_3),
    para("Here we can use the shorter Product Over Sum equation as we only have two parallel resistors:"),
    para("R_P1 = (R1 x R2) / (R1 + R2) = 37 x 24 / 37 + 24 = 416/61"),
    para("R_P1 = 11.0491 + R3 + R₄"),
    para("R_T = 11.049 + 58 = 75.0491"),
    para("R_T = 7Ω"),
    italicPara("(Prepare for a Self check and Task Sheet, please provide a sheet of paper as answer sheet)"),

    heading("Self Check No. 1.2.1", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Resistors, Color coding, Conversion, Tolerance, Circuits and Ohm's Law)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Instruction: choose the letter of the correct answer."),
    numberedItem(1, "It is an electronic device that resists, limits or opposes the amount of current in a circuit:"),
    para("   a. resistor\n   b. capacitor\n   c. diode\n   d. none of the above"),
    numberedItem(2, "A kind of resistor that can varies the resistance:"),
    para("   a. Fixed resistor\n   b. Potentiometer\n   c. Volume control\n   d. Variable resistor"),
    numberedItem(3, "A circuit with only one possible path that the electrical current may flow:"),
    para("   a. Parallel\n   b. Series\n   c. Series - Parallel\n   d. none of the above"),
    numberedItem(4, "the computation of accepted value of resistor's resistance from minimum to maximum:"),
    para("   a. decoding\n   b. conversion\n   c. tolerance\n   d. none of the above"),
    numberedItem(5, "It states that an electrical circuit's current is directly proportional to its voltage, and inversely proportional to its resistance:"),
    para("   a. Fuse law\n   b. ohm's law\n   c. volt's law\n   d. none of the above"),

    heading("Self Check No. 1.2.2", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Resistors, Color coding, Conversion, Tolerance, Circuits and Ohm's Law)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Instruction: Write and illustrate the resistor's color coding table in exact order."),
    simpleTable(
      ["COLOR", "DIGIT", "MULTIPLIER", "TOLERANCE"],
      Array(11).fill(["", "", "", ""])
    ),

    heading("Self Check No. 1.2.3", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Resistors, Color coding, Conversion, Tolerance, Circuits and Ohm's Law)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Resistor's Color Coding, Decoding and Conversion"),
    bold("Instruction: Give the given value of each color of resistor 1-5 and give the color of each given value of resistor 6-10."),
    numberedItem(1, "Brown    Black    Red    Gold"),
    numberedItem(2, "Green    Blue    Yellow    Gold"),
    numberedItem(3, "Violet    Red    Orange    Silver"),
    numberedItem(4, "Yellow    White    Gold    Gold"),
    numberedItem(5, "Orange    Orange    Brown    Gold"),
    emptyPara(),
    numberedItem(6, "680 KΩ ± 5%"),
    numberedItem(7, "26 KΩ ± 10%"),
    numberedItem(8, "3.8 MΩ ± 5%"),
    numberedItem(9, "4.7 KΩ ± 10%"),
    numberedItem(10, "30 Ω ± 5%"),

    heading("Self Check No. 1.2.4", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Resistors, Color coding, Conversion, Tolerance, Circuits and Ohm's Law)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Resistor's Tolerance"),
    bold("Instruction: Give all the details of each Resistor, identify the color and value, write down the minimum and maximum tolerance (1 pts. Each)."),
    numberedItem(1, "Red    Blue    Red    Gold    Silver"),
    numberedItem(2, "Gray    Orange    Yellow    Gold"),
    numberedItem(3, "White    Blue    Brown    Gold"),
    numberedItem(4, "Blue    Black    Gold    Gold"),
    numberedItem(5, "Violet    Red    Gold    Silver"),
    emptyPara(),
    numberedItem(6, "940 KΩ ± 5%"),
    numberedItem(7, "77 KΩ ± 10%"),
    numberedItem(8, "56 MΩ ± 5%"),
    numberedItem(9, "4.7 KΩ ± 10%"),
    numberedItem(10, "87 Ω ± 5%"),

    heading("Homework No. 1.2.1", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Resistors, Color coding, Conversion, Tolerance, Circuits and Ohm's Law)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Resistor's Tolerance"),
    bold("Instruction: Give all the details of each Resistor, identify the color and value, write down the minimum and maximum tolerance (3 pts. Each)."),
    numberedItem(1, "Yellow    Violet    Yellow    Gold"),
    numberedItem(2, "Green    Blue    Brown    Gold"),
    numberedItem(3, "Red    White    Orange    Silver"),
    numberedItem(4, "Brown    Black    Red    Gold"),
    numberedItem(5, "Gray    Orange    Green    Silver"),
    emptyPara(),
    numberedItem(6, "10 KΩ ± 5%"),
    numberedItem(7, "950 KΩ ± 10%"),
    numberedItem(8, "8.2 MΩ ± 5%"),
    numberedItem(9, "1.2 KΩ ± 10%"),
    numberedItem(10, "100 Ω ± 5%"),

    heading("Task Sheet No. 1.2.1", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Checking and Testing of Resistor)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Performance Objectives:"),
    para("To understand the value of color coded resistor, to maximize the use of Multi-meter and to identify the conditions of a resistor."),
    bold("Procedures:"),
    numberedItem(1, "Decode the color thru value"),
    numberedItem(2, "Compute the minimum and maximum tolerance"),
    numberedItem(3, "Using the suggested ohm's range, check using the multi-meter"),
    numberedItem(4, "Write down the parts, description (R1,R2,etc.), actual reading (resistance), the range use and the remarks if good or defective"),
    numberedItem(5, "Submit to Trainer for checking"),
    emptyPara(),
    simpleTable(
      ["PARTS", "DESCRIPTION", "ACTUAL READING", "RANGE", "REMARKS"],
      [["1", "", "", "", ""], ["2", "", "", "", ""], ["3", "", "", "", ""], ["4", "", "", "", ""], ["5", "", "", "", ""]]
    ),

    heading("Performance Criteria 1.2.1", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Checking and Testing of Resistor)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bullet("Observe and follow safety policies and procedures?"),
    bullet("Use proper personal protective equipment?"),
    bullet("Did I identify different kind of electronic components?"),
    bullet("Follow measuring and correct ranging of Multi-meter?"),
    bullet("I used tools and equipments properly?"),

    heading("Performance Criteria Checklist 1.2.1", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Checking and Testing of Resistor)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    simpleTable(
      ["RATING", "REMARKS"],
      [["1", "Poor"], ["2", "Fair"], ["3", "Good"], ["4", "Satisfactory"], ["5", "Excellent"]]
    ),
  ];
  await createDoc("Information Sheet 1.2.docx", children);
}

// ===================== SHEET 1.3 =====================
async function sheet13() {
  const children = [
    heading("INFORMATION SHEET No. 1.3"),
    new Paragraph({ text: "Capacitors and Diodes", alignment: AlignmentType.CENTER, spacing: { after: 240 } }),

    heading("DEVICES: CAPACITORS", HeadingLevel.HEADING_2),
    mixedPara([
      "A ",
      { text: "capacitor", bold: true },
      " is an electronic device or components that store or accumulate electrical energy in a circuit. A capacitor is a tool consisting of two conductive plates, each of which hosts an opposite charge. These plates are separated by a dielectric or other form of insulator, which helps them maintain an electric charge."
    ]),
    mixedPara([
      { text: "Farad", italics: true },
      " – is the measured unit of a capacitance, the higher the farad, the longer it holds the stored charges; the lower the farad, the shorter it holds the stored charges."
    ]),
    para("Capacitance is the quantity of electrical energy that a capacitor can store. Capacitance is dependent upon:"),
    bullet("Thickness of dielectric"),
    bullet("Area of the plates"),
    bullet("Constant of dielectric"),

    para("The capacitor's functions are as follows:"),
    mixedPara([{ text: "Coupling", italics: true }, " – to prevent DC from entering the circuit."]),
    mixedPara([{ text: "De-coupling", italics: true }, " – to prevent or protect the circuit from another circuit."]),
    mixedPara([{ text: "Noisefilters or Snubbers", italics: true }, " – to protect the circuit from distortion and interference."]),
    mixedPara([{ text: "Motorstarter", italics: true }, " – is used for starting the motor."]),
    mixedPara([{ text: "Tuned-in Circuit", italics: true }, " – is used for tuning circuit."]),

    heading("Types of Capacitors", HeadingLevel.HEADING_3),
    bullet("Polystyrene (Film capacitor)"),
    bullet("Mylar (for high voltage charge)"),
    bullet("Polyethylene (Film capacitor)"),
    bullet("Mica (for high voltage charge)"),
    bullet("Tantalum (electrolytic type)"),
    bullet("Ceramic (for small amount of charge)"),

    mixedPara([
      { text: "Electrolytic capacitors", bold: true },
      " have several prominent characteristics, including:"
    ]),
    para("a. High capacitance-to-size ratio;"),
    para("b. Polarity sensitivity and terminals marked + and -;"),
    para("c. Allow more leakage current than other types; and"),
    para("d. have their C value and voltage rating printed on them."),
    para("The main advantage of the electrolytic capacitor is the large capacitance-per-size factor. Two obvious disadvantages are the polarity, which must be observed, and the higher leakage current feature."),

    heading("Variable Capacitor", HeadingLevel.HEADING_3),
    para("A variable capacitor is a special type of capacitor, most commonly used for tuning ratios, which allows the amount of electrical charge it can hold to be altered over a certain range, measured in a unit known as farads. Regular capacitors build up and store an electrical charge until it's ready to use. While a variable capacitor stores the charge in the same fashion, it can be adjusted as many times as desired to store different amounts of electricity."),
    para("Two types of variable capacitors include air variable capacitors and vacuum variable capacitors."),

    heading("How to test the capacitor using multi-tester?", HeadingLevel.HEADING_3),
    bold("Good Capacitor:"),
    para("(Testing electrolytic capacitor: Ohmmeter connection - OHMS, Range - R x 1, Forward Resistance - Low, Capacitance - depends on value, Condition - Good)"),
    bold("Shorted Capacitor:"),
    para("(Testing electrolytic capacitor: Range - R x 1, Forward Resistance - Zero, Condition - Shorted)"),
    bold("Open Capacitor:"),
    para("(Testing electrolytic capacitor: Range - R x 10, Forward Resistance - Infinite, Capacitance - 470uF, Condition - Open)"),
    bold("Leaky Capacitor:"),
    para("(Testing electrolytic capacitor: Range - R x 10, Forward Resistance - Zero, Capacitance - 100 uF, Condition - Leaky)"),

    heading("DEVICES: DIODE", HeadingLevel.HEADING_2),
    para("A diode is a semi-conductor device that permits the flow of current in only one direction. A Diode can convert electric current from AC to DC or from Alternating Current to Direct Current. This is called Rectification, and rectifier diodes are most commonly used in low current power supplies."),
    para("Rectification is a process of converting AC to DC."),
    para("A diode has 2 parts:"),
    para("Anode (A) +       Cathode (K) -"),

    heading("TYPES OF DIODE", HeadingLevel.HEADING_3),
    bold("1. Rectifier Diode - Converts AC to DC."),
    emptyPara(),
    bold("2. Signal Diode"),
    para("Both diodes work the same way by allowing current to flow in one direction. The differences have to do with power and frequency characteristics. They are made from a p-n junction and are two lead devices."),
    para("Small signal diodes have much lower power and current ratings, around 150mA, 500mW maximum compared to rectifier diodes, they can also function better in high frequency applications or in clipping and switching applications with short-duration pulse waveforms."),
    para("- converts Intermediate Frequency (IF) to Audio Frequency (AF)."),

    bold("3. Regulator Diode - used for voltage regulation."),
    emptyPara(),
    bold("4. Temperature Dependent Diode - used for temperatures hot or cold to automatically on and off."),
    emptyPara(),
    bold("5. Light Emitting Diode (L.E.D)"),
    para("is a semiconductor light source. LEDs are used as indicator lamps in many devices and are increasingly used for other lighting. Photodiode or Photo Sensitive Diode - Allows current flow when exposed to light (vice versa)."),
    emptyPara(),
    bold("6. Photodiode (Photosensitive diode)"),
    para("is a type of photodetector capable of converting light into either current or voltage, depending upon the mode of operation. The common, traditional solar cell used to generate electric solar power is a large area photodiode."),

    heading("How to test the Diode using multi-tester?", HeadingLevel.HEADING_3),
    bold("Good Diode:"),
    para("(Ohmmeter connection: Range R x 10, Forward Resistance - LOW (Not zero/not infinite), Part No. - 1N4001, Reverse: Range R x 10, Reverse Resistance - Infinite, Condition - Good)"),
    bold("Shorted Diode:"),
    para("(Range R x 1, Forward Resistance - Zero, Reverse: Range R x 1, Reverse Resistance - Zero, Condition - Shorted)"),
    bold("Open Diode:"),
    para("(Range R x 10, Forward Resistance - LOW (Not zero/not infinite), Part No. - 1N2Rs, Reverse: Range R x 10, Reverse Resistance - Infinite/zero, Condition - Open)"),
    bold("Leaky Diode:"),
    para("(Range R x 1, Forward Resistance - Zero, Reverse: Range R x 1, Reverse Resistance - Zero, Condition - Shorted)"),

    italicPara("(Prepare for a Self check and Task Sheet, please provide a sheet of paper as answer sheet)"),

    heading("Self Check No. 1.3.1", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Capacitors and diodes)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Instruction: Enumeration"),
    para("1-4. Give at least 4 kinds of diodes and illustrate the schematic symbol."),
    para("5-8. Give at least 4 kinds of capacitors."),
    para("9-10. 2 parts of Diode"),
    emptyPara(),
    bold("Instruction: write down the condition of each measured components as leaky, shorted, open or good. (2 pts. Each)"),
    para("(Refer to diagrams showing various multi-tester readings for diodes and capacitors)"),

    heading("Task Sheet No. 1.3.1", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Checking and Testing of Capacitor)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Performance Objectives:"),
    para("To understand the value of capacitor, to intensify the use of Multi-meter and to identify the conditions of a capacitor."),
    bold("Procedures:"),
    numberedItem(1, "Check the value of capacitor"),
    numberedItem(2, "Check the polarity of the terminal"),
    numberedItem(3, "Using the suggested range in testing a capacitor"),
    numberedItem(4, "Use the tester in Reverse bias"),
    numberedItem(5, "Write down the parts, description (C1,C2,etc.), findings, the range use and the remarks if good or defective such as leaky, shorted or open."),
    numberedItem(6, "Submit to Trainer for checking"),
    simpleTable(
      ["PARTS", "DESCRIPTION", "FINDINGS", "RANGE", "REMARKS"],
      [["1", "", "", "", ""], ["2", "", "", "", ""], ["3", "", "", "", ""], ["4", "", "", "", ""], ["5", "", "", "", ""]]
    ),

    heading("Task Sheet No. 1.3.2", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Checking and Testing of Diode)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Performance Objectives:"),
    para("To understand the value of diode, to intensify the use of Multi-meter and to identify the conditions of a diode."),
    bold("Procedures:"),
    numberedItem(1, "Check the type of diode"),
    numberedItem(2, "Check the polarity of the terminal"),
    numberedItem(3, "Using the multi-meter, check by performing both reverse and forward bias"),
    numberedItem(4, "Write down the parts, description (D1,D2,etc.), findings, the range use and the remarks if good or defective such as leaky, shorted or open."),
    numberedItem(5, "Submit to Trainer for checking"),
    simpleTable(
      ["PARTS", "DESCRIPTION", "FINDINGS", "RANGE", "REMARKS"],
      [["1", "", "", "", ""], ["2", "", "", "", ""], ["3", "", "", "", ""], ["4", "", "", "", ""], ["5", "", "", "", ""]]
    ),

    heading("Performance Criteria 1.3.1 & 1.3.2", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Checking and Testing of Capacitor and Diode)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bullet("Observe and follow safety policies and procedures?"),
    bullet("Use proper personal protective equipment?"),
    bullet("Did I identify different kind of electronic components?"),
    bullet("Follow measuring and correct ranging of Multi-meter?"),
    bullet("I used tools and equipments properly?"),

    heading("Performance Criteria Checklist 1.3.1 & 1.3.2", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Checking and Testing of Capacitor and Diode)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    simpleTable(
      ["RATING", "REMARKS"],
      [["1", "Poor"], ["2", "Fair"], ["3", "Good"], ["4", "Satisfactory"], ["5", "Excellent"]]
    ),
  ];
  await createDoc("Information Sheet 1.3.docx", children);
}

// ===================== SHEET 1.4 =====================
async function sheet14() {
  const children = [
    heading("INFORMATION SHEET No. 1.4"),
    new Paragraph({ text: "Transistors, Integrated Circuit's (IC's) and Transformers", alignment: AlignmentType.CENTER, spacing: { after: 240 } }),

    heading("DEVICES: TRANSISTOR", HeadingLevel.HEADING_2),
    mixedPara([
      "It is an electronic semi-device which provides ",
      { text: "oscillation, amplification, switching", italics: true },
      " and ",
      { text: "rectification", italics: true },
      " of electrical current. The principal materials used are germanium and silicon. Basically, there are two kinds of transistors, namely:"
    ]),
    mixedPara([
      { text: "Oscillation", italics: true }, " - A process of moving back and forth, to and from."
    ]),
    mixedPara([
      { text: "Amplification", italics: true }, " - A process of converting."
    ]),
    numberedItem(1, "Positive Negative Positive (PNP)"),
    numberedItem(2, "Negative Positive Negative (NPN)"),
    para("A transistor is an electronic amplifying device with 2 junction type. Transistors are the NPN and PNP impurity materials utilized to determine the conductivity type of a semi-conductor."),
    para("A PNP transistor is made by sandwiching a slab of silicon with \"N-type\" impurities between the 2 layers of silicon with \"P-type\" semi-conductor material. Silicon is generally used for NPN bipolar with N-type semi-conductor materials because of its lower internal voltage drop. Germanium is used for some power type transistor."),
    para("Transistors are composed of three parts – a base, a collector, and an emitter. The base is the gate controller device for the larger electrical supply. The collector is the larger electrical input, and the emitter is the outlet for that supply."),
    para("B – Base – Input"),
    para("C – Collector – output"),
    para("E – Emitter – Ground"),

    heading("SILICON CONTROLLED RECTIFIER", HeadingLevel.HEADING_3),
    mixedPara([
      "A ",
      { text: "silicon-controlled rectifier (or semiconductor-controlled rectifier)", bold: true },
      " is a four-layer solid state current controlling device. The name \"silicon controlled rectifier\" or ",
      { text: "SCR", bold: true },
      " is General Electric's trade name for a type of thyristor."
    ]),
    para("SCRs are unidirectional devices (i.e. can conduct current only in one direction) as opposed to TRIACs which are bidirectional (i.e. current can flow through them in either direction). SCRs can be triggered normally only by currents going into the gate as opposed to TRIACs which can be triggered normally by either a positive or a negative current applied to its gate electrode."),

    heading("DEVICES: INTEGRATED CIRCUIT (IC)", HeadingLevel.HEADING_2),
    para("Integrated Circuits (ICs) are used in all types of modern electronic devices. They are integrated, meaning that they are made as a total circuit and housed in one enclosure. The enclosure may take a number of shapes, it may be similar to a 2-5 transistor package with 8 leads instead of 3, it may be what is referred to as a dual in-line package (DIP) with as many as 24 leads. All components are manufactured as a common unit."),
    para("There are two major kinds of ICs:"),
    numberedItem(1, "Analog (or linear) which are used as amplifiers, timers and oscillators"),
    numberedItem(2, "Digital (or logic) which is used in microprocessors and memories"),
    para("Some ICs are combinations of both analog and digital."),
    para("There are 3 categories of IC packages:"),
    numberedItem(1, "Small scale integration (SSI)"),
    numberedItem(2, "Medium scale Integration (MSI)"),
    numberedItem(3, "Large scale integration."),
    para("The SSI package generally has fewer than 200 components in it, MSI and LSI package may have anywhere from 1000 to 256,000 or more components."),
    para("Keep in mind that transistors, diodes, and resistors and capacitors are referred to as discrete components, and an IC may have thousands of these discrete components located on one chip. The concept of having thousand of part on one chip so small that the human eye cannot see is hard to believe when you have been working with vacuum tubes and transistor to increase this number of components on a chip."),
    para("ICs can be used to do any number of things electronically; they are classified further according to their function. The two broad categories of classification here are digital and linear."),

    bold("Dual In-line Package (DIP) IC"),
    para("This is an electronic device package with a rectangular housing and two parallel rows of electrical connecting pins. The package may be through-hole mounted to a printed circuit board or inserted in a socket."),
    bold("Linear IC"),
    para("This is a solid-state analog device characterized by a theoretically infinite number of possible operating states. It operates over a continuous range of input levels. In contrast, a digital IC has a finite number of discrete input and output states."),
    bold("Ball Grid Array (BGA) IC"),
    para("This is a type of surface-mount packaging used for integrated circuits. Ball-grid array (BGA) packages are used to permanently mount devices such as microprocessors."),
    bold("Surface-Mounted Device (SMD) IC"),
    para("This is a method for constructing electronic circuits in which the components are mounted directly onto the surface of printed circuit boards (PCBs). An electronic device so made is called a surface-mount device (SMD)."),

    heading("DEVICES: TRANSFORMERS & POWERSUPPLY", HeadingLevel.HEADING_2),
    heading("Types of Power Supply", HeadingLevel.HEADING_3),
    para("There are many types of power supply. Most are designed to convert high voltage AC mains electricity to a suitable low voltage supply for electronic circuits and other devices. A power supply can be broken down into a series of blocks, each of which performs a particular function."),
    para("For example a full regulated supply:"),
    bold("Block Diagram of a Regulated Power Supply System:"),
    para("220V AC Mains → Transformer → Rectifier → Smoothing → Regulator → Regulated 5V DC"),
    para("Each of the blocks is described in more detail below:"),
    mixedPara([{ text: "Transformer", italics: true }, " - steps down high voltage AC mains to low voltage AC."]),
    mixedPara([{ text: "Rectifier", italics: true }, " - converts AC to DC, but the DC output is varying."]),
    mixedPara([{ text: "Smoothing", italics: true }, " - smooths the DC from varying greatly to a small ripple."]),
    mixedPara([{ text: "Regulator", italics: true }, " - eliminates ripple by setting DC output to a fixed voltage."]),

    para("Power supplies made from these blocks are described below with a circuit diagram and a graph of their output:"),
    bullet("Transformer only"),
    bullet("Transformer + Rectifier"),
    bullet("Transformer + Rectifier + Smoothing"),
    bullet("Transformer + Rectifier + Smoothing + Regulator"),

    bold("Transformer only"),
    para("Input: high voltage AC (mains supply) → Output: low voltage AC"),
    para("The low voltage AC output is suitable for lamps, heaters and special AC motors. It is not suitable for electronic circuits unless they include a rectifier and a smoothing capacitor."),

    bold("Transformer + Rectifier"),
    para("Output: varying DC"),
    para("The varying DC output is suitable for lamps, heaters and standard motors; it is not suitable for electronic circuits unless they include a smoothing capacitor."),

    bold("Transformer + Rectifier + Smoothing"),
    para("Output: smooth DC"),
    para("The smooth DC output has a small ripple. It is suitable for most electronic circuits."),

    bold("Transformer + Rectifier + Smoothing + Regulator"),
    para("Output: regulated DC"),
    para("The regulated DC output is very smooth with no ripple. It is suitable for all electronic circuits."),

    bold("Dual Supplies"),
    para("Some electronic circuits require a power supply with positive and negative outputs as well as zero volts (0V). This is called a dual supply because it is like two ordinary supplies connected together as shown in the diagram."),
    para("Dual supplies have three outputs, for example a 15V supply has +9V, 0V and -9V outputs."),

    heading("Transformer", HeadingLevel.HEADING_3),
    para("Transformers convert AC electricity from one voltage to another with little loss of power. Transformers work only with AC and this is one of the reasons why mains electricity is AC."),
    para("Step up transformers increase voltage, step-down transformers reduce voltage. Most power supplies use a step-down transformer to reduce the dangerously high mains voltage (220V) to a safer low voltage."),
    bold("3 Parts of Transformer:"),
    para("Core, Primary, Secondary"),
    para("The input coil is called the primary and the output coil is called the secondary. There is no electrical connection between the two coils, instead they are linked by an alternating magnetic field created in the soft iron core of the transformer. The two lines in the middle of the circuit symbol represent the core."),
    para("Transformers waste very little power so the power out is (almost) equal to the power in. Note that as voltage is stepped down current is stepped up."),
    para("The ratio of the number of turns on each coil, called the turn's ratio, determines the ratio of the voltages. A step-down transformer has a large number of turns on its primary (input) coil which is connected to the high voltage mains supply, and a small number of turns on its secondary (output) coil to give a low output voltage."),

    bold("Kinds of transformer:"),
    numberedItem(1, "Power Transformer"),
    numberedItem(2, "Isolation Transformer"),
    numberedItem(3, "Auto Transformer"),
    numberedItem(4, "Audio Transformer"),
    numberedItem(5, "RF and IF Transformer"),
    bold("Types of Power Transformer:"),
    bullet("Center Tap Transformer"),
    bullet("Multi Tap Transformer"),

    heading("Rectifier", HeadingLevel.HEADING_3),
    para("There are several ways of connecting diodes to make a rectifier to convert AC to DC. A full-wave rectifier, the bridge rectifier is the most important and it produces full-wave varying DC. But this method is costly. It can also be made from just two diodes if a center-tap transformer is used. A single diode can be used as a rectifier but it only produces half-varying DC."),

    bold("Bridge rectifier"),
    para("A bridge rectifier can be made using four individual diodes, but it is also available in special packages containing the four diodes required. It is called a full-wave rectifier because it uses all the AC wave (both positive and negative sections). 1.4V is used up in the bridge rectifier because each diode uses 0.7V when conducting and there are always two diodes conducting, as shown in the diagram below."),
    para("Bridge rectifier: Alternate pairs of diodes conduct, changing over the connections so the alternating directions of AC are converted to the one direction of DC."),
    para("Output: full-wave varying DC (using all the AC wave)"),

    bold("Single diode rectifier"),
    para("A single diode can be used as a rectifier but this produces half-wave varying DC which has gaps when the AC is negative. It is hard to smooth this sufficiently well to supply electronic circuits unless they require a very small current so the smoothing capacitor does not significantly discharge during the gaps."),
    para("Output: half-wave varying DC (using only half the AC wave)"),

    heading("Smoothing", HeadingLevel.HEADING_3),
    mixedPara([
      "Smoothing is performed by a large value ",
      { text: "electrolytic capacitor", bold: true },
      " connected across the DC supply to act as a reservoir, supplying current to the output when the varying DC voltage from the rectifier is falling. The diagram shows the unsmoothed varying DC (dotted line) and the smoothed DC (solid line)."
    ]),
    para("The capacitor charges quickly near the peak of the varying DC, and then discharges as it supplies current to the output."),
    para("Smoothing is not perfect due to the capacitor voltage falling a little as it discharges, giving a small ripple voltage. For many circuits a ripple which is 10% of the supply voltage is satisfactory and the equation below gives the required value for the smoothing capacitor. A larger capacitor will give fewer ripples. The capacitor value must be doubled when smoothing half-wave DC."),

    heading("Regulator", HeadingLevel.HEADING_3),
    para("Voltage regulator ICs are available with fixed (usually 5, 12 and 15V) or variable output voltages. They are also rated by the maximum current they can pass. Negative voltage regulators are available, mainly for use in dual supplies. Most regulators include some automatic protection from excessive current ('overload protection') and overheating ('thermal protection')."),
    para("Many of the fixed voltage regulator ICs has 3 leads and look like power transistors, such as the 7805 +5V 1A regulator shown on the right. They include a hole for attaching a heatsink if necessary."),

    italicPara("(Prepare for a Self check and Task Sheet, please provide a sheet of paper as answer sheet)"),

    heading("Self Check No. 1.4.1", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Transistors, Integrated Circuit's (IC's) and Transformers)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Instruction: choose the letter of the correct answer."),
    numberedItem(1, "It is a semi conductor device that can be used for amplification, oscillation, rectification and switching"),
    para("   a. Transistor\n   b. Capacitor\n   c. Diode\n   d. none of the above"),
    numberedItem(2, "A type of IC with a rectangular housing and two parallel rows of electrical connecting pin"),
    para("   a. Linear IC\n   b. Dual In-line Package (DIP) IC\n   c. Surface Mounted Device (SMD) IC\n   d. None of the above"),
    numberedItem(3, "An electro-magnetic device that is commonly used for step-down of voltage"),
    para("   a. Power supply\n   b. Integrated circuit\n   c. Transformer\n   d. none of the above"),
    numberedItem(4, "All types of modern electronic devices. They are integrated, meaning that they are made as a total circuit and housed in one enclosure"),
    para("   a. Power supply\n   b. Integrated circuit\n   c. Transistor\n   d. none of the above"),
    numberedItem(5, "It is a process of a transistor, that allows to move back and forth"),
    para("   a. Amplification\n   b. Rectification\n   c. Oscillation\n   d. none of the above"),
    para("6-7. Give at least two (2) types of integrated circuit and illustrate how it look like."),
    para("8-10. What are the three (3) parts of Transistor"),
    para("11-13. What are the three (3) parts of Transformers"),
    para("14-15. Give at least two (2) types of rectification process"),

    heading("Task Sheet No. 1.4.1", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Checking and Testing of Transistor)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Performance Objectives:"),
    para("To understand the value of capacitor, to intensify the use of Multimeter and to identify the conditions of a capacitor."),
    bold("Procedures:"),
    numberedItem(1, "Check the value of Transistor"),
    numberedItem(2, "Using the suggested range in testing a terminal of Transistor in finding the base, emitter and collector"),
    numberedItem(3, "Use the tester in both reverse and forward bias"),
    numberedItem(4, "Write down the parts, description (Q1,Q2,etc.), findings, the range use and the remarks if good or defective such as leaky, shorted or open."),
    numberedItem(5, "Submit to Trainer for checking"),
    simpleTable(
      ["PARTS", "DESCRIPTION", "FINDINGS", "RANGE", "REMARKS"],
      [["1", "", "", "", ""], ["2", "", "", "", ""], ["3", "", "", "", ""], ["4", "", "", "", ""], ["5", "", "", "", ""]]
    ),

    heading("Performance Criteria 1.4.1", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Checking and Testing of Transistor)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bullet("Observe and follow safety policies and procedures?"),
    bullet("Use proper personal protective equipment?"),
    bullet("Did I identify different kind of electronic components?"),
    bullet("Follow measuring and correct ranging of Multi-meter?"),
    bullet("I used tools and equipments properly?"),

    heading("Performance Criteria Checklist 1.4.1", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Checking and Testing of Transistor)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    simpleTable(
      ["RATING", "REMARKS"],
      [["1", "Poor"], ["2", "Fair"], ["3", "Good"], ["4", "Satisfactory"], ["5", "Excellent"]]
    ),
  ];
  await createDoc("Information Sheet 1.4.docx", children);
}

// ===================== SHEET 1.5 =====================
async function sheet15() {
  const children = [
    heading("INFORMATION SHEET No. 1.5"),
    new Paragraph({ text: "Schematic Diagram, Pictorial Diagram, Block Diagram and PCB Making", alignment: AlignmentType.CENTER, spacing: { after: 240 } }),

    heading("SCHEMATIC DIAGRAM", HeadingLevel.HEADING_2),
    para("A drawing showing all significant components, parts, or tasks (and their interconnections) of a circuit, device, flow, process, or project by means of standard symbols."),
    para("A connection of Resistors in series and parallel is also a schematic diagram, below is a sample of complicated diagrams where capacitors, and diodes are added in series – parallel connections."),

    heading("PICTORIAL DIAGRAM", HeadingLevel.HEADING_2),
    para("A simplified diagram which shows the various components of a system (motorcycle, car, ship, electronic devices, airplane, etc) without regard to their physical location, how the wiring is marked, or how the wiring is routed. It does, however, show you the sequence in which the components are connected."),

    heading("SAMPLE OF PICTORIAL AND SCHEMATIC SYMBOL", HeadingLevel.HEADING_2),
    simpleTable(
      ["COMPONENT TYPES", "PICTORIAL REPRESENTATIONS", "SCHEMATIC SYMBOLS"],
      [
        ["Conductors (connected)", "+", "+"],
        ["Conductors (not connected)", "+", "+"],
        ["Cell", "(cell image)", "(cell symbol)"],
        ["Battery", "(battery image)", "(battery symbol)"],
        ["Switch (SPST) Single-pole Single-throw", "(switch image)", "(switch symbol)"],
      ]
    ),

    heading("ADDITIONAL SCHEMATIC AND PICTORIAL SYMBOLS", HeadingLevel.HEADING_3),
    para("Capacitor-Non Polarized"),
    para("Capacitor-Polarized"),
    para("Resistor"),
    para("Variable Resistor"),
    para("Diode"),
    para("Light Emitting Diode (LED)"),
    para("Symbols for Ground"),

    heading("SCHEMATIC DIAGRAM OF FLIP-FLOP", HeadingLevel.HEADING_3),
    para("Components: 470R resistors (x2), 10k resistors (x2), 100u capacitors (x2), LED1 (Red LED), LED2 (Green LED), Q1 BC 547, Q2 BC 547, Sw (switch), 9v battery"),

    heading("SCHEMATIC DIAGRAM OF FULL-WAVE BRIDGE TYPE MULTI TAP TRANSFORMER", HeadingLevel.HEADING_3),
    para("220V input with selector switch, multiple voltage taps (3v, 4.5v, 6v, 9v, 12v), bridge rectifier, and output connections."),
    para("Selector Switch / 6 Way Rotary"),
    para("D1-D4: 4004/06"),
    para("R1-R3: 1K ohms"),
    para("F: 1000uf/35volts"),

    heading("PRINTED CIRCUIT BOARD MAKING", HeadingLevel.HEADING_2),
    bold("Step 1: Materials"),
    numberedItem(1, "Copper Clad (2x2)"),
    numberedItem(2, "Masking Tape"),
    numberedItem(3, "Ruler"),
    numberedItem(4, "Knife Cutter"),
    numberedItem(5, "Pencil"),
    numberedItem(6, "Mini Drill"),
    numberedItem(7, "Ferric Chloride"),
    numberedItem(8, "Sand Paper"),
    numberedItem(9, "Plastic Container"),

    bold("Step 2: Designing the circuit"),
    para("Using paper and pencil design the layout of the circuit, it is easiest to do this as a top view of the board, it helps to also have all the different components on hand to help with spacing and placement. As a side note also make sure to design the layout so that it will fit on the board. If you already have a pre designed layout you can skip this part."),

    bold("Step 3: Drawing the traces"),
    para("Next you will want to make a copy of the design that is a reverse of the original, if you drew it in reverse or the one you have is already reversed just make a regular copy of it. Cut the copy of the layout out with scissors leaving some on either side so you can fold it around the PCB and tape it in place. Now using the tape, tape the design onto the copper side of the PCB. With the #65 drill bit use the layout to drill a hole in the center of all the solder pads for the individual components."),

    bold("Step 4: Cutting the Excess"),
    para("-Gently glide the knife along the edge of your desired PCB design."),
    para("-Strip the excess masking tape to reveal the design of your work"),
    para("-Try not to puncture the design to prevent damage to the connection that will affect the functionality of your circuit during the etching process"),

    bold("Step 5: Etching"),
    para("-Start off by finding a clean dry place where you can safely etch the circuit board, preferably outside."),
    para("-Take your small container and pour about 1/4 to 1/2\" of Ferric Chloride into it."),
    para("-Fill the larger container with warm water about 1\" deep."),
    para("-Drop PCB into the Ferric Chloride, copper side up and place the small container into the water in the larger container."),
    para("-Gently rock the small container in the water in so to keep the FC moving which helps with the etching process."),
    para("-in about 5-7 minutes you should start to see the copper start to dissolve away, notice the areas where the traces a drawn are unaffected."),
    para("-After about 10-12 minutes the board should be completely etched, at which time you should immediately remove the PCB and drop it into the water in the larger container to rinse it and then dry it off on the paper towel."),
    para("-When you are done put the cover on the small container, you can use the Ferric Chloride over again a few times, and pour out the water in the larger container and rinse it out. You can use the larger container to store the small container and your extra Ferric Chloride that is still in the original bottle."),

    bold("Step 6: Cleaning the PCB"),
    para("-using 1000 sand paper clean the Sharpie off the traces."),

    italicPara("(Prepare for a Self check and Task Sheet, please provide a sheet of paper as answer sheet)"),

    heading("Self Check No. 1.5.1", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Schematic Diagram, Pictorial Diagram, Block Diagram and PCB Making)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Termination of Schematic Diagram"),
    bold("Instruction: Interpret the given Schematic Diagram by drawing and connecting its Pictorial Diagram."),
    para("(Three schematic diagrams provided for student interpretation)"),

    heading("Home Work No. 1.5.1", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Schematic Diagram, Pictorial Diagram, Block Diagram and PCB Making)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Termination of Schematic Diagram"),
    bold("Instruction: Interpret the given Schematic Diagram by drawing and connecting its Pictorial Diagram in a piece of paper."),
    para("(Three schematic diagrams provided for homework)"),

    heading("Home Work No. 1.5.2", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Schematic Diagram, Pictorial Diagram, Block Diagram and PCB Making)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Instruction:"),
    numberedItem(1, "Re-draw the Flip-Flop Schematic Diagram"),
    numberedItem(2, "Illustrate the Layout of the given Schematic in a Piece of Paper"),
    para("Note: Draw it on a sheet of paper without any Erasures and Alterations."),

    heading("Task Sheet No. 1.5.1", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Drawing and Tracing of Inventory - Linear Power Supply)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Performance Objectives:"),
    para("To draw and trace electronic components and connection; to intensify the use of inventory / linear power supply; and to identify the result of it."),
    bold("Procedures:"),
    numberedItem(1, "Check all parts and components"),
    numberedItem(2, "Draw the schematic and write all values"),
    numberedItem(3, "Write the description, designation, qty, actual reading, range and remarks"),
    numberedItem(4, "Add the total value of each parts"),
    numberedItem(5, "Submit to Trainer for checking"),
    simpleTable(
      ["PARTS", "DESCRIPTION", "DESIGNATION", "QTY", "ACTUAL READING", "RANGE", "REMARKS"],
      [
        ["a", "L.E.D.", "", "", "", "", ""],
        ["b", "1N4007/06", "", "", "", "", ""],
        ["c", "Transformer", "", "", "", "", ""],
      ]
    ),

    heading("Task Sheet No. 1.5.2", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Drawing and Tracing of Inventory - Flip-Flop)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Performance Objectives:"),
    para("Checking and tracing of Inventory by using the multi-meter and tracing its connection for accuracy."),
    bold("Procedures:"),
    numberedItem(1, "Check all parts and components"),
    numberedItem(2, "Draw the schematic and write all the values of each components"),
    numberedItem(3, "Add the total value of the parts, describe each component, write the designation, qty, actual reading, range and remarks of it."),
    numberedItem(4, "Submit to Trainer for checking"),
    simpleTable(
      ["PARTS", "DESCRIPTION", "DESIGNATION", "QTY", "ACTUAL READING", "RANGE", "REMARKS"],
      [
        ["a", "L.E.D.", "", "", "", "", ""],
        ["b", "DICP", "", "", "", "", ""],
        ["c", "1N4007/06", "", "", "", "", ""],
        ["d", "BC548 Transistor", "", "", "", "", ""],
      ]
    ),

    heading("Performance Criteria 1.5.1 & 1.5.2", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Checking and Testing of Inventory - Linear Power Supply & Flip-flop)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bullet("Observe and follow safety policies and procedures?"),
    bullet("Use proper personal protective equipment?"),
    bullet("Did I identify different kind of electronic components?"),
    bullet("Follow measuring and correct ranging of Multi-meter?"),
    bullet("I used tools and equipments properly?"),

    heading("Performance Criteria Checklist 1.5.1 & 1.5.2", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Checking and Testing of Inventory - Linear Power Supply & Flip-flop)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    simpleTable(
      ["RATING", "REMARKS"],
      [["1", "Poor"], ["2", "Fair"], ["3", "Good"], ["4", "Satisfactory"], ["5", "Excellent"]]
    ),
  ];
  await createDoc("Information Sheet 1.5.docx", children);
}

// ===================== SHEET 1.6 =====================
async function sheet16() {
  const children = [
    heading("INFORMATION SHEET No. 1.6"),
    new Paragraph({ text: "Soldering and De-soldering, Terminaling and Connecting, Troubleshooting Process", alignment: AlignmentType.CENTER, spacing: { after: 240 } }),

    heading("SOLDERING PROCESS", HeadingLevel.HEADING_2),
    heading("SOLDERING IS EASY - HERE'S HOW TO DO IT", HeadingLevel.HEADING_3),
    para("Step-by-step soldering guide:"),
    numberedItem(1, "Get your tools ready: soldering iron, solder, wet sponge"),
    numberedItem(2, "Place the tip of the iron on the joint"),
    numberedItem(3, "Feed solder to the joint (not the iron)"),
    numberedItem(4, "Remove the solder, then the iron"),
    numberedItem(5, "Inspect the joint - it should be shiny and smooth"),
    para("Tips: Clean the tip regularly on a wet sponge. Heat both parts of the joint. Apply solder to the joint, not the iron. Use the right amount of solder. Keep the iron tip clean and tinned."),

    heading("What is Soldering and Desoldering?", HeadingLevel.HEADING_2),
    para("Soldering is a process in which two or more items are joined together by melting and putting a filler metal (solder) into the joint, the filler metal having a lower melting point than the adjoining metal. Soldering differs from welding in that soldering does not involve melting the work pieces. In brazing, the work piece metal also does not melt, but the filler metal is one that melts at a higher temperature than in soldering. In the past, nearly all solders contained lead, but environmental and health concerns have increasingly dictated use of lead-free alloys for electronics and plumbing purposes."),
    para("Soldering is a process of connecting/joining two metallic surfaces (e.g. terminals of components and the PCB copper pads) with the use of a soldering iron and a solder lead. This process is commonly used in electronics for permanent electrical connections between electronic components/parts on a Printed Circuit Board (PCB). There are three types of soldering that are commonly used:"),
    numberedItem(1, "Soft soldering"),
    numberedItem(2, "Hard soldering (silver soldering and brazing)"),
    numberedItem(3, "Braze welding"),
    para("Soft soldering, which uses tin/lead alloy as its filler metal, is the most widely used for making connections in the electronics field. Solder paste is used then types of soldering to be selected to modify the melting point."),
    para("Not all soldering processes produce the same results; there are many types of soldering to be selected for which type the correct soldering should be done."),

    heading("SOLDERING AND DESOLDERING TOOLS", HeadingLevel.HEADING_2),
    bold("A soldering iron"),
    para("is a hand tool used in soldering. It supplies heat to melt solder so that it can flow into the joint between two workpieces. A soldering iron is composed of a heated metal tip and an insulated handle. For home-based electronics work use a soldering iron in the range of 15W to 30W for best results. It would be preferable that the tip is a fine conical type for precision soldering work, in electronic assembly."),

    bold("A soldering gun"),
    para("is an approximately pistol-shaped, electrically powered tool for soldering metals using tin-based solder to achieve a strong mechanical bond with good electrical contact. The body of the tool contains a transformer with a primary winding connected to mains electricity through a trigger switch in the handle, and a single-turn secondary winding of thick copper with very low resistance. Pressing the trigger causes a current to flow through the copper tip, which is resistively heated."),

    bold("Tweezers (Pliers)"),
    para("are tools used for picking up objects too small to be easily handled with the human fingers."),

    bold("A Solder sucker"),
    para("is a device used to remove solder from a printed circuit board (PCB). Its purpose is to aid in desoldering, the process of removing components from the board via the application of heat and removal of solder from the connection. It is usually a manually operated vacuum pump, which can be used to remove molten solder, or it can be the de-solder wick, usually a braided copper wire which uses capillary action to remove the solder from previously soldered connection."),

    heading("SOLDERING SUPPLIES AND MATERIALS", HeadingLevel.HEADING_2),
    para("Due to the extremely small size of modern electronic components, it is sometimes necessary to use a magnifying glass or binocular microscope during the process of hand soldering. Excessive application of heat can damage sensitive components. While the PCB itself and the immediate surrounding area should not heat up significantly during the soldering process, overheating can destroy parts and loosen copper foils. Choosing the right type of solder for each situation is important for making durable connections."),

    bold("Solder"),
    para("is a fusible metal alloy used to create a permanent bond between metal workpieces. Solder is melted in order to adhere to and connect the pieces after cooling, which requires that an alloy suitable for use as solder have a lower melting point than the pieces being joined. The solder should also be resistant to oxidative and corrosive effects that would degrade the joint over time. Solder used in making electrical connections also needs to have favorable electrical characteristics."),

    mixedPara([
      { text: "Tin-lead solders,", bold: true },
      " also called soft solders, are commercially available with tin concentrations between 5% and 70% by weight. The greater the tin concentration, the greater the solder's tensile and shear strengths. For electrical and electronic work, 60/40 (Sn/Pb) solder is principally used for electrical/electronic soldering."
    ]),

    para("Due to health concerns associated with lead, the manufacture and use of lead-based solders is being eliminated. Significant lead-free solders include tin, copper, silver, bismuth, indium, zinc, antimony, and traces of other metals in varying amounts. Contact at the melting points from 5 to 20 °C higher, though solder."),

    bold("Flux"),
    para("is a reducing agent designed to help reduce (return oxidized metals to their metallic state) metal oxides at the points of contact to improve the electrical connection and mechanical strength. Two principal types of flux are acid flux, used for metal mending and plumbing, and rosin flux, used in electronics and plumbing; rosin flux is preferred in electronics since acid flux is corrosive and can damage delicate circuitry."),

    mixedPara([
      { text: "Solder paste", italics: true },
      " (or solder cream) is used to connect the leads of integrated circuits and other components to a circuit board in surface mount technology. The paste consists of a flux and tiny spheres of solder."
    ]),

    mixedPara([
      { text: "Solder wick", italics: true },
      " or desoldering braid is a pre-fluxed copper braid used to remove solder. It consists of braided strands of copper wire coated with flux. A typical use is in the removal of excess solder, to correct a solder bridge, or to desolder a connection so that a component can be replaced. The braid may also be used without flux, but it is less efficient and more likely to leave small balls of solder on the board. The flux in the solder braid will flow into the molten solder."
    ]),

    para("Solder paste (or solder cream) is used to connect the leads of IC and other surface mounted components to the PCB. Soldering paste is a pre-mixed blend of small spheres of solder combined with flux."),

    heading("SMD SOLDERING", HeadingLevel.HEADING_2),
    para("Surface mount components, at the time this article is written, are by far the most commonly used components in the market. With the continuous trend of miniaturization SMDs are available in Packages smaller than 0.4 x 0.2 mm. Reflowed Solder at SMDs."),
    para("Surface mounting of SMD components is performed by a process called \"reflow\" soldering. A stencil with apertures which line up with the contact pads is placed over the PCB, and solder paste is applied (normally by screen printing, though jet printing is used in some applications). SMDs are then placed onto the solder paste using automated pick-and-place equipment, and the entire board is heated."),

    heading("SOLDERING DEFECTS", HeadingLevel.HEADING_2),
    bold("Common defects:"),
    bullet("Cold solder joint: caused when the solder cools too quickly or parts move during cooling. It has a dull, grainy appearance. A dry joint is weak mechanically and a poor conductor."),
    bullet("Disturbed joint: caused from touching the solder before it is set. Similar to a cold joint, but less severe."),
    bullet("Overheating: caused by leaving the iron on the joint for too long, which can damage components."),
    bullet("Insufficient wetting: when solder doesn't flow properly. Results in poor connection."),
    bullet("Solder bridge: unwanted solder connecting adjacent tracks or pads on PCB."),
    para("A dry joint appears as a rough, uneven surface that has a lumpy, crystallized texture. It has been caused by moving the joint while the solder was still liquid, or by the iron being taken off the joint too soon, or by putting too little solder on the joint."),
    para("In practice, it has been found that adding solder to correct a joint gives poor results, because the fresh solder melts at a lower temperature than the contaminated joint. A dry joint is weak mechanically and a poor conductor."),

    heading("GOOD JOINT vs BAD JOINT", HeadingLevel.HEADING_3),
    para("To ensure a good (soldered) joint, it should be smooth, bright and shiny, without sharp projections. The solder should be bright and has the form or shape like a small \"volcano\" (inverted cone)."),
    para("A bad joint looks dull, has lumps, has air pockets or voids, or has a weak or cold solder appearance."),

    para("In electronics, a 'veroboard' or 'stripboard' is used then more boards (matrix/PCB); a perfboard (proto board), is a material for prototyping of electronic circuits. It is made of thin, rigid copper-clad or the copper track (strip off). Excessive heat or force may pull off the copper from the board, particularly on single sided PCBs without through-hole plating."),

    bold("Here are some helpful tips to help you solder:"),
    para("a) good, all surfaces wet with solder"),
    para("b) too little solder"),
    para("c) too much solder, may hide a bad solder joint/no connection"),
    para("d) pad, etch or surface not soldered properly, the solder has not wetted the surfaces"),
    para("e) solder bridge, may cause a short circuit on close tracks"),
    para("f) solder is making a \"ball\" and has not wet the surfaces, the soldered joint might look good, but may have limited contact between the solder and the surface, particularly on single sided PCBs without through-hole plating."),

    heading("Job Sheet No. 1.6.1", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Soldering and De-soldering, Terminaling and Connecting, Troubleshooting Process)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Performance Objectives:"),
    para("To apply correct and proper technique on how soldering; know and understand soldering procedures used for connecting electronic components and repair."),
    bold("Supplies and Materials needed:"),
    para("Soldering iron, lead, flux, PCB, and passive components"),
    para("Multi-tester, Magnifier, Sand Paper"),
    bold("Recommended Method:"),
    para("Demonstration and Observation"),
    para("Ask the trainer (for those students of safety usage)"),
    para("And make/do the proper exercise of solder(ing)."),
    emptyPara(),
    bold("SCHEMATIC DIAGRAM OF FULL-WAVE BRIDGE TYPE MULTI TAP TRANSFORMER"),
    para("(See diagram with 220v input, selector switch, bridge rectifier D1-D4, and output)"),

    heading("Job Sheet No. 1.6.2", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Soldering and De-soldering, Terminaling and Connecting, Troubleshooting Process)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bold("Soldering Objectives:"),
    para("To apply correct and proper technique of how soldering; should know and understand soldering procedures used for connecting electronic components and assembly."),
    bold("Performance Objectives:"),
    para("Supplies and Materials needed:"),
    para("Soldering iron, lead, flux, and passive components"),
    para("C, R, LED (red + green) x4 pcs each, Transistor x2"),
    para("1.5 LED green x1 / red x2 (4 pcs each)"),
    para("Perfboard with copper clad: 1x3"),
    bold("Tools and Equipment:"),
    para("Multi-tester, Cutter, Long Nose, Magnifier"),
    bold("Recommended Method:"),
    para("Demonstration of assembly/soldering"),
    para("Hands-on exercise (Plug and Play)"),
    para("Follow schematic Diagram slide and test."),
    emptyPara(),
    bold("SCHEMATIC DIAGRAM OF FLIP-FLOP"),
    para("Components: 470R (x2), 10k (x2), 100u (x2), LED1 Red, LED2 Green, Q1 BC 547, Q2 BC 547, Sw, 9v"),

    heading("Performance Criteria 1.6.1 & 1.6.2", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Soldering and De-soldering, Terminaling and Connecting, Troubleshooting Process)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    bullet("Observe and follow safety policies and procedures?"),
    bullet("Use proper personal protective equipment?"),
    bullet("Did I identify different kind of electronic components?"),
    bullet("Follow soldering and de-soldering techniques?"),
    bullet("I used tools and equipments properly?"),

    heading("Performance Criteria Checklist 1.6.1 & 1.6.2", HeadingLevel.HEADING_2),
    new Paragraph({ text: "(Soldering and De-soldering, Terminaling and Connecting, Troubleshooting Process)", alignment: AlignmentType.CENTER, spacing: { after: 120 } }),
    simpleTable(
      ["RATING", "REMARKS"],
      [["1", "Poor"], ["2", "Fair"], ["3", "Good"], ["4", "Satisfactory"], ["5", "Excellent"]]
    ),
  ];
  await createDoc("Information Sheet 1.6.docx", children);
}

// Run all
(async () => {
  try {
    await sheet11();
    await sheet12();
    await sheet13();
    await sheet14();
    await sheet15();
    await sheet16();
    console.log("\nAll 6 docx files created successfully!");
  } catch (err) {
    console.error("Error:", err);
  }
})();
