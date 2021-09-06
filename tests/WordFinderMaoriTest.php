<?php

namespace CustomD\WordFinder\Tests;

use CustomD\WordFinder\Facades\WordFinder;
use CustomD\WordFinder\Grid;
use CustomD\WordFinder\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase;
use RuntimeException;

class WordFinderMaoriTest extends TestCase
{

    protected $words = [
        "hui",
        "marae",
        "nau mai",
        "tangihanga",
        "tangi",
        "karanga",
        "manuhiri",
        "tangata whenua",
        "whaikōrero",
        "kaikōrero",
        "kaiwhai kōrero",
        "haka",
        "waiata",
        "koha",
        "whare nui",
        "hongi",
        "karakia",
        "pōwhiri",
        "māori",
        "te reo māori",
        "whare whakairo",
        "whare kai",
        "whare horoi",
        "hangi",
        "moko",
        "pā",
        "pātaka",
        "poi",
        "pounamu",
        "tiki",
        "whare",
        "utu",
        "aroha",
        "ihi",
        "mana",
        "manaakitanga",
        "mauri",
        "noa",
        "raupatu",
        "rohe",
        "taihoa",
        "tapu",
        "tiaki",
        "taonga",
        "tino rangatiratanga",
        "tūrangawaewae",
        "wehi",
        "whakapapa",
        "whenua",
        "ariki",
        "hapū",
        "iwi",
        "kaumātua",
        "kōtiro",
        "ngāi tātou",
        "pākehā",
        "rangatira",
        "tama",
        "tamāhine",
        "tamaiti",
        "tamariki",
        "tamatāne",
        "tāne",
        "teina",
        "tipuna",
        "tuahine",
        "tuakana",
        "tungāne",
        "wahine",
        "waka",
        "whāngai",
        "whānau",
        "whanaunga",
        "au",
        "awa",
        "ao",
        "iti",
        "kai",
        "manga",
        "mānia",
        "marama",
        "maunga",
        "moana",
        "motu",
        "nui",
        "ō",
        "one",
        "pae",
        "papa",
        "pari",
        "poto",
        "puke",
        "puna",
        "rangi",
        "roa",
        "roto",
        "tai",
        "tomo",
        "wai",
        "whanga",
        "e noho rā",
        "haere rā",
        "haere mai",
        "hei konā rā",
        "kia ora",
        "mauri ora",
        "mōrena",
        "tēnā koe",
        "tēnā kōrua",
        "tēnā koutou",
        "arero",
        "hope",
        "kēkē",
        "kanohi",
        "ihu",
        "kakī",
        "karu",
        "maikuku",
        "kauae",
        "kūhā",
        "kumu",
        "māhunga",
        "makawe",
        "manawa",
        "matimati",
        "niho",
        "poho",
        "pakihiwi",
        "pona",
        "puku",
    ];




    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'word-finder' => WordFinder::class,
        ];
    }

    public function testDefaultConfigs()
    {
        $config = config('word-finder');
        $this->assertArrayHasKey('default_min_word_length', $config);
        $this->assertArrayHasKey('default_max_word_length', $config);
        $this->assertArrayHasKey('default_grid_size', $config);
    }

    public function testGridThrowsExceptionifGridSizeToSmall()
    {
        $this->expectException(RuntimeException::class);
        WordFinder::create(collect($this->words), 2);
    }

    public function testGridLoadsWithDefaultConfigs()
    {
        config(['word-finder.character_map' => \CustomD\WordFinder\CharacterMaps\Maori::class]);
        //$grid = WordFinder::create(collect($this->words), 12, 3, 12);
        $grid = $this->generateGridSet(10);
        $this->assertInstanceOf(Grid::class, $grid);
        $this->assertIsArray($grid->getPuzzleWords()->toArray());
        $this->assertIsString($grid->getTextGrid());
        $this->assertIsArray($grid->getGrid());

        $this->assertCount(12, $grid->getGrid());
        foreach ($grid->getGrid() as $row) {
            $this->assertCount(12, collect($row)->filter());
        }

   //     dd($grid->getTextGrid(), $grid->getPuzzleWords()->toArray());
    }

    protected function generateGridSet(int $count = 1)
    {
        for ($i=0; $i<=$count; $i++) {
            $grid = WordFinder::create(collect($this->words), 12, 3, 12);
        }
        return $grid;
    }
}
