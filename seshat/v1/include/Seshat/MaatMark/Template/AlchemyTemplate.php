<?php

namespace Seshat\MaatMark\Template;

use Seshat\MaatMark;
use Seshat\MaatMark\GlyphTemplate;

class AlchemyTemplate extends GlyphTemplate {
	public static $alchemy = array(
		// Elements:
		'fire'=>'&#x1F702;',
		'air'=>'&#x1F701;',
		'water'=>'&#x1F704;',
		'earth'=>'&#x1F703;',

		// Alchemical symbols:
		'quintessence'=>'&#x1F700;',                  'aquafortis'=>'&#x1F705;',                 'aqua-regia'=>'&#x1F706;',
		'aqua-regia-2'=>'&#x1F707;',                  'aqua-vitae'=>'&#x1F708;',                 'aqua-vitae-2'=>'&#x1F709;',
		'vinegar'=>'&#x1F70A;',                       'vinegar-2'=>'&#x1F70B;',                  'vinegar-3'=>'&#x1F70C;',
		'sulfur'=>'&#x1F70D;',                        'philosophers-sulfur'=>'&#x1F70E;',        'black-sulfur'=>'&#x1F70F;',
		'mercury-sublimate'=>'&#x1F710;',             'mercury-sublimate-2'=>'&#x1F711;',        'mercury-sublimate-3'=>'&#x1F712;',
		'cinnabar'=>'&#x1F713;',                      'salt'=>'&#x1F714;',                       'nitre'=>'&#x1F715;',
		'vitriol'=>'&#x1F716;',                       'vitriol-2'=>'&#x1F717;',                  'rock-salt'=>'&#x1F718;',
		'rock-salt-2'=>'&#x1F719;',                   'gold'=>'&#x1F71A;',                       'silver'=>'&#x1F71B;',
		'iron-ore'=>'&#x1F71C;',                      'iron-ore-2'=>'&#x1F71D;',                 'crocus-of-iron'=>'&#x1F71E;',
		'regulus-of-iron'=>'&#x1F71F;',               'copper-ore'=>'&#x1F720;',                 'iron-copper-ore'=>'&#x1F721;',
		'sublimate-of-copper'=>'&#x1F722;',           'crocus-of-copper'=>'&#x1F723;',           'crocus-of-copper-2'=>'&#x1F724;',
		'copper-antimoniate'=>'&#x1F725;',            'salt-of-copper-antimoniate'=>'&#x1F726;', 'sublimate-of-salt-of-copper'=>'&#x1F727;',
		'verdigris'=>'&#x1F728;',                     'tin-ore'=>'&#x1F729;',                    'lead-ore'=>'&#x1F72A;',
		'antimony-ore'=>'&#x1F72B;',                  'sublimate-of-antimony'=>'&#x1F72C;',      'salt-of-antimony'=>'&#x1F72D;',
		'sublimate-of-salt-of-antimony'=>'&#x1F72E;', 'vinegar-of-antimony'=>'&#x1F72F;',        'regulus-of-antimony'=>'&#x1F730;',
		'regulus-of-antimony-2'=>'&#x1F731;',         'regulus'=>'&#x1F732;',                    'regulus-2'=>'&#x1F733;',
		'regulus-3'=>'&#x1F734;',                     'regulus-4'=>'&#x1F735;',                  'alkali'=>'&#x1F736;',
		'alkali-2'=>'&#x1F737;',                      'marcasite'=>'&#x1F738;',                  'sal-ammoniac'=>'&#x1F739;',
		'arsenic'=>'&#x1F73A;',                       'realgar'=>'&#x1F73B;',                    'realgar-2'=>'&#x1F73C;',
		'auripigment'=>'&#x1F73D;',                   'bismuth-ore'=>'&#x1F73E;',                'tartar'=>'&#x1F73F;',
		'tartar-2'=>'&#x1F740;',                      'quick-lime'=>'&#x1F741;',                 'borax'=>'&#x1F742;',
		'borax-2'=>'&#x1F743;',                       'borax-3'=>'&#x1F744;',                    'alum'=>'&#x1F745;',
		'oil'=>'&#x1F746;',                           'spirit'=>'&#x1F747;',                     'tincture'=>'&#x1F748;',
		'gum'=>'&#x1F749;',                           'wax'=>'&#x1F74A;',                        'powder'=>'&#x1F74B;',
		'calx'=>'&#x1F74C;',                          'tutty'=>'&#x1F74D;',                      'caput-mortuum'=>'&#x1F74E;',
		'scepter-of-jove'=>'&#x1F74F;',               'caduceus'=>'&#x1F750;',                   'trident'=>'&#x1F751;',
		'starred-trident'=>'&#x1F752;',               'lodestone'=>'&#x1F753;',                  'soap'=>'&#x1F754;',
		'urine'=>'&#x1F755;',                         'horse-dung'=>'&#x1F756;',                 'ashes'=>'&#x1F757;',
		'pot-ashes'=>'&#x1F758;',                     'brick'=>'&#x1F759;',                      'powdered-brick'=>'&#x1F75A;',
		'amalgam'=>'&#x1F75B;',                       'stratum-super-stratum'=>'&#x1F75C;',      'stratum-super-stratum-2'=>'&#x1F75D;',
		'sublimation'=>'&#x1F75E;',                   'precipitate'=>'&#x1F75F;',                'distill'=>'&#x1F760;',
		'dissolve'=>'&#x1F761;',                      'dissolve-2'=>'&#x1F762;',                 'purify'=>'&#x1F763;',
		'putrefaction'=>'&#x1F764;',                  'crucible'=>'&#x1F765;',                   'crucible-2'=>'&#x1F766;',
		'crucible-3'=>'&#x1F767;',                    'crucible-4'=>'&#x1F768;',                 'crucible-5'=>'&#x1F769;',
		'alembic'=>'&#x1F76A;',                       'bath-of-mary'=>'&#x1F76B;',               'bath-of-vapours'=>'&#x1F76C;',
		'retort'=>'&#x1F76D;',                        'hour'=>'&#x1F76E;',                       'night'=>'&#x1F76F;',
		'day-night'=>'&#x1F770;',                     'month'=>'&#x1F771;',                      'half-dram'=>'&#x1F772;',
		'half-ounce'=>'&#x1F773;',
	);

	public function expand() {
		return $this->expandGlyphs('alchemy',self::$alchemy);
	}
}


