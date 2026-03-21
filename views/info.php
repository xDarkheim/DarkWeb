<div class="page-title"><span><?php echo lang('module_titles_txt_17'); ?></span></div>

<!-- HERO BANNER -->
<div class="info-hero">
	<div class="info-hero-text">
		<div class="info-hero-title">Welcome to <?php echo $serverName; ?></div>
		<div class="info-hero-sub"><?php echo $season; ?> &mdash; <?php echo $expType; ?> server. A realm of dark fantasy, epic battles and legendary power awaits. Rise through the ranks, forge powerful alliances and conquer the forces of darkness.</div>
	</div>
</div>

<!-- SERVER RATES -->
<div class="info-section-title"><i class="bi bi-bar-chart-fill"></i> Server Rates</div>
<div class="info-rates-grid">
	<div class="info-rate-card">
		<div class="info-rate-icon">⚔️</div>
		<div class="info-rate-label">Experience</div>
		<div class="info-rate-value"><?php echo $exp; ?></div>
	</div>
	<div class="info-rate-card">
		<div class="info-rate-icon">🧿</div>
		<div class="info-rate-label">Master Experience</div>
		<div class="info-rate-value"><?php echo $masterExp; ?></div>
	</div>
	<div class="info-rate-card">
		<div class="info-rate-icon">📦</div>
		<div class="info-rate-label">Item Drop</div>
		<div class="info-rate-value"><?php echo $drop; ?></div>
	</div>
	<div class="info-rate-card">
		<div class="info-rate-icon">🎯</div>
		<div class="info-rate-label">Max Level</div>
		<div class="info-rate-value"><?php echo $maxLevel; ?></div>
	</div>
	<div class="info-rate-card">
		<div class="info-rate-icon">🔄</div>
		<div class="info-rate-label">Max Reset</div>
		<div class="info-rate-value"><?php echo $maxReset; ?></div>
	</div>
	<div class="info-rate-card">
		<div class="info-rate-icon">🌐</div>
		<div class="info-rate-label">Season</div>
		<div class="info-rate-value" style="font-size:14px;"><?php echo $season; ?></div>
	</div>
</div>

<!-- CHARACTER CLASSES -->
<div class="info-section-title"><i class="bi bi-shield-fill-exclamation"></i> Character Classes</div>
<div class="info-classes-grid info-classes-7">

	<div class="info-class-card">
		<div class="info-class-header dk">
			<div class="info-class-name">Dark Knight</div>
			<div class="info-class-role">Warrior</div>
		</div>
		<div class="info-class-body">
			<p>The frontline warrior of MU. Masters of melee combat, wielding swords, axes and maces with devastating force. Evolves into <strong>Blade Knight</strong> and ultimately <strong>Blade Master</strong>.</p>
			<div class="info-class-stats">
				<span class="stat-bar-wrap"><span class="stat-name">Strength</span><span class="stat-bar"><span style="width:95%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Agility</span><span class="stat-bar"><span style="width:55%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Vitality</span><span class="stat-bar"><span style="width:80%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Energy</span><span class="stat-bar"><span style="width:20%"></span></span></span>
			</div>
		</div>
	</div>

	<div class="info-class-card">
		<div class="info-class-header dw">
			<div class="info-class-name">Dark Wizard</div>
			<div class="info-class-role">Mage</div>
		</div>
		<div class="info-class-body">
			<p>Master of arcane destruction. Commands fire, ice and lightning to annihilate enemies from afar. Evolves into <strong>Soul Master</strong> and ultimately <strong>Grand Master</strong>.</p>
			<div class="info-class-stats">
				<span class="stat-bar-wrap"><span class="stat-name">Strength</span><span class="stat-bar"><span style="width:20%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Agility</span><span class="stat-bar"><span style="width:40%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Vitality</span><span class="stat-bar"><span style="width:50%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Energy</span><span class="stat-bar"><span style="width:99%"></span></span></span>
			</div>
		</div>
	</div>

	<div class="info-class-card">
		<div class="info-class-header elf">
			<div class="info-class-name">Fairy Elf</div>
			<div class="info-class-role">Support / Archer</div>
		</div>
		<div class="info-class-body">
			<p>Swift archer and healer. Buffs allies, buffs defense and delivers precision ranged damage. Evolves into <strong>Muse Elf</strong> and ultimately <strong>High Elf</strong>.</p>
			<div class="info-class-stats">
				<span class="stat-bar-wrap"><span class="stat-name">Strength</span><span class="stat-bar"><span style="width:35%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Agility</span><span class="stat-bar"><span style="width:95%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Vitality</span><span class="stat-bar"><span style="width:45%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Energy</span><span class="stat-bar"><span style="width:65%"></span></span></span>
			</div>
		</div>
	</div>

	<div class="info-class-card">
		<div class="info-class-header mg">
			<div class="info-class-name">Magic Gladiator</div>
			<div class="info-class-role">Hybrid</div>
		</div>
		<div class="info-class-body">
			<p>Unlocked at level 220. A unique hybrid combining melee power with magical spells. Evolves into <strong>Duel Master</strong>. Does not require a third class change.</p>
			<div class="info-class-stats">
				<span class="stat-bar-wrap"><span class="stat-name">Strength</span><span class="stat-bar"><span style="width:75%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Agility</span><span class="stat-bar"><span style="width:70%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Vitality</span><span class="stat-bar"><span style="width:60%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Energy</span><span class="stat-bar"><span style="width:70%"></span></span></span>
			</div>
		</div>
	</div>

	<div class="info-class-card">
		<div class="info-class-header dl">
			<div class="info-class-name">Dark Lord</div>
			<div class="info-class-role">Commander</div>
		</div>
		<div class="info-class-body">
			<p>Unlocked at level 250. Commands a loyal Familiar in battle and leads parties with powerful aura buffs. Evolves into <strong>Lord Emperor</strong>.</p>
			<div class="info-class-stats">
				<span class="stat-bar-wrap"><span class="stat-name">Strength</span><span class="stat-bar"><span style="width:85%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Agility</span><span class="stat-bar"><span style="width:50%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Vitality</span><span class="stat-bar"><span style="width:70%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Command</span><span class="stat-bar"><span style="width:99%"></span></span></span>
			</div>
		</div>
	</div>

	<div class="info-class-card">
		<div class="info-class-header sum">
			<div class="info-class-name">Summoner</div>
			<div class="info-class-role">Debuffer / Caster</div>
		</div>
		<div class="info-class-body">
			<p>Masters dark summoning magic, cursing enemies and calling powerful creatures to fight at her side. Evolves into <strong>Bloody Summoner</strong> and ultimately <strong>Dimension Master</strong>.</p>
			<div class="info-class-stats">
				<span class="stat-bar-wrap"><span class="stat-name">Strength</span><span class="stat-bar"><span style="width:25%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Agility</span><span class="stat-bar"><span style="width:55%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Vitality</span><span class="stat-bar"><span style="width:45%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Energy</span><span class="stat-bar"><span style="width:90%"></span></span></span>
			</div>
		</div>
	</div>

	<div class="info-class-card">
		<div class="info-class-header rf">
			<div class="info-class-name">Rage Fighter</div>
			<div class="info-class-role">Brawler</div>
		</div>
		<div class="info-class-body">
			<p>A fearless martial artist who fights barehanded with explosive power. Evolves into <strong>Fist Master</strong>. Does not require a third class change.</p>
			<div class="info-class-stats">
				<span class="stat-bar-wrap"><span class="stat-name">Strength</span><span class="stat-bar"><span style="width:90%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Agility</span><span class="stat-bar"><span style="width:65%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Vitality</span><span class="stat-bar"><span style="width:85%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Energy</span><span class="stat-bar"><span style="width:30%"></span></span></span>
			</div>
		</div>
	</div>

	<div class="info-class-card">
		<div class="info-class-header gl">
			<div class="info-class-name">Grow Lancer</div>
			<div class="info-class-role">Lancer</div>
		</div>
		<div class="info-class-body">
			<p>A powerful spear fighter who charges into battle with devastating momentum. Evolves into <strong>Mirage Lancer</strong> and ultimately <strong>Shining Lancer</strong>.</p>
			<div class="info-class-stats">
				<span class="stat-bar-wrap"><span class="stat-name">Strength</span><span class="stat-bar"><span style="width:85%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Agility</span><span class="stat-bar"><span style="width:75%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Vitality</span><span class="stat-bar"><span style="width:70%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Energy</span><span class="stat-bar"><span style="width:25%"></span></span></span>
			</div>
		</div>
	</div>

	<div class="info-class-card">
		<div class="info-class-header rw">
			<div class="info-class-name">Rune Wizard</div>
			<div class="info-class-role">Rune Mage</div>
		</div>
		<div class="info-class-body">
			<p>Commands ancient rune magic, weaving powerful spells with elemental force and arcane mastery. Evolves into <strong>Rune Spell Master</strong> and ultimately <strong>Grand Rune Master</strong>.</p>
			<div class="info-class-stats">
				<span class="stat-bar-wrap"><span class="stat-name">Strength</span><span class="stat-bar"><span style="width:20%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Agility</span><span class="stat-bar"><span style="width:45%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Vitality</span><span class="stat-bar"><span style="width:40%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Energy</span><span class="stat-bar"><span style="width:99%"></span></span></span>
			</div>
		</div>
	</div>

	<div class="info-class-card">
		<div class="info-class-header sl">
			<div class="info-class-name">Slayer</div>
			<div class="info-class-role">Assassin</div>
		</div>
		<div class="info-class-body">
			<p>A deadly vampire assassin who drains life from enemies and moves with supernatural speed. Evolves into <strong>Royal Slayer</strong> and ultimately <strong>Master Slayer</strong>.</p>
			<div class="info-class-stats">
				<span class="stat-bar-wrap"><span class="stat-name">Strength</span><span class="stat-bar"><span style="width:70%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Agility</span><span class="stat-bar"><span style="width:90%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Vitality</span><span class="stat-bar"><span style="width:55%"></span></span></span>
				<span class="stat-bar-wrap"><span class="stat-name">Energy</span><span class="stat-bar"><span style="width:50%"></span></span></span>
			</div>
		</div>
	</div>

</div>

<!-- GAME FEATURES -->
<div class="info-section-title"><i class="bi bi-lightning-charge-fill"></i> Game Features</div>
<div class="info-features-grid">
	<div class="info-feature-card"><div class="info-feature-icon">🏰</div><div class="info-feature-name">Castle Siege</div><div class="info-feature-desc">The ultimate guild war. Two alliances battle to capture and hold the Lorencia Castle — the ruling guild gains powerful bonuses and prestige until the next siege.</div></div>
	<div class="info-feature-card"><div class="info-feature-icon">💥</div><div class="info-feature-name">Chaos Castle</div><div class="info-feature-desc">A battle royale style event. Dozens of players fight on a crumbling platform above the abyss — last one standing claims rare rewards.</div></div>
	<div class="info-feature-card"><div class="info-feature-icon">🩸</div><div class="info-feature-name">Blood Castle</div><div class="info-feature-desc">Race through waves of undead to repair the Archangel's weapon before time runs out.</div></div>
	<div class="info-feature-card"><div class="info-feature-icon">👹</div><div class="info-feature-name">Devil Square</div><div class="info-feature-desc">Survive relentless monster waves inside a sealed arena. The more kills, the higher the rewards.</div></div>
	<div class="info-feature-card"><div class="info-feature-icon">🗿</div><div class="info-feature-name">Illusion Temple</div><div class="info-feature-desc">A team PvP objective event. Two sides compete to steal the statue from the enemy base.</div></div>
	<div class="info-feature-card"><div class="info-feature-icon">⚙️</div><div class="info-feature-name">Kanturu Relics</div><div class="info-feature-desc">Enter the ancient ruins and fight for control of the Maya boss to earn Gemstone and Ruud.</div></div>
	<div class="info-feature-card"><div class="info-feature-icon">👑</div><div class="info-feature-name">Golden Invasion</div><div class="info-feature-desc">Golden monsters invade the world at set intervals. Track them down to earn Goblin Points and rare materials.</div></div>
	<div class="info-feature-card"><div class="info-feature-icon">⚔️</div><div class="info-feature-name">Arka War</div><div class="info-feature-desc">The eternal conflict between Duprian and Vanrtarion Gens factions. Earn exclusive Gens rewards.</div></div>
	<div class="info-feature-card"><div class="info-feature-icon">🪞</div><div class="info-feature-name">Doppelganger</div><div class="info-feature-desc">Enter a mirror dimension and fight through waves of your own shadow to earn ancient-tier equipment.</div></div>
</div>

<!-- WORLD MAPS -->
<div class="info-section-title"><i class="bi bi-globe-americas"></i> World of MU — Maps</div>
<div class="info-maps-grid">
	<div class="info-map-item"><span class="info-map-dot"></span>Lorencia</div>
	<div class="info-map-item"><span class="info-map-dot"></span>Dungeon</div>
	<div class="info-map-item"><span class="info-map-dot"></span>Devias</div>
	<div class="info-map-item"><span class="info-map-dot"></span>Noria</div>
	<div class="info-map-item"><span class="info-map-dot"></span>Lost Tower</div>
	<div class="info-map-item"><span class="info-map-dot"></span>Atlans</div>
	<div class="info-map-item"><span class="info-map-dot"></span>Tarkan</div>
	<div class="info-map-item"><span class="info-map-dot"></span>Icarus</div>
	<div class="info-map-item"><span class="info-map-dot"></span>Aida</div>
	<div class="info-map-item"><span class="info-map-dot"></span>Crywolf Fortress</div>
	<div class="info-map-item"><span class="info-map-dot"></span>Kalima</div>
	<div class="info-map-item"><span class="info-map-dot"></span>Land of Trials</div>
	<div class="info-map-item"><span class="info-map-dot"></span>Kanturu</div>
	<div class="info-map-item"><span class="info-map-dot"></span>Elbeland</div>
	<div class="info-map-item"><span class="info-map-dot"></span>Swamp of Calmness</div>
	<div class="info-map-item"><span class="info-map-dot"></span>Raklion</div>
	<div class="info-map-item"><span class="info-map-dot"></span>Vulcanus</div>
	<div class="info-map-item"><span class="info-map-dot"></span>Karutan</div>
</div>

