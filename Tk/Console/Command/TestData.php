<?php
namespace Tk\Console\Command;

use Tk\Console\Console;

class TestData extends Console
{

    public function createUniqueEmail(string $username = ''): string
    {
        if (!$username) {
            $username = hash('crc32', microtime());
        }
        return $username . '@' . $this->createDomain();
    }

    public function createEmail(): string
    {
        return strtolower($this->createName()) . '@' . $this->createDomain();
    }

    public function createWww(): string
    {
        return  'http://www.' . $this->createDomain() . '/';
    }

    public function createPhone(): string
    {
        return  sprintf('%s %s %s', $this->createNumberStr(2), $this->createNumberStr(4), $this->createNumberStr(4));
    }

    public function createAddress(): string
    {
        return  sprintf('%s %s %s, %s', $this->createNumberStr(rand(1,4)), $this->createName(), 'Street', 'Melbourne, VIC, 3001');
    }

    public function createDomain(): string
    {
        return  strtolower($this->createName()).'.com' . ((rand(1,5)==1) ? '.au' : '');
    }

    public function createCourseName(): string
    {
        $names = array('VETS', 'AG', 'SCI', 'DVM');
        return $names[rand(0, count($names)-1)] . '-' . $this->createNumberStr(6);
    }

    public function createFullName(): string
    {
        return $this->createName() . ' ' . $this->createName();
    }

    public function createName(): string
    {
        $names = array('Andy','Bart','Charles','Denny','Eveline','Femke','Gismo','Harold','Imke','Jan','Kees','Lissane','Mark','Norris','Opa','Pieter','Quebec','Ralf','Stephen','Tamara','Ursula','Verdinant','Willem','Xanté','Yankee','Zuly');
        return $names[rand(0, count($names)-1)];
    }

    public function createNumberStr(int $len = 8): string
    {
        $str = [];
        for ($i = 0; $i < $len ;$i++) {
            $str[] = rand(0, 9);
        }
        return implode('', $str);
    }

    public function createStr(int $len = 12, string $chars = 'abcdefghijklmnopqrstuvwxyz'): string
    {
        return substr(str_shuffle($chars), 0, $len);
    }

    public function createWords(int $cnt = 2, int $len = 0, string $chars = 'abcdefghijklmnopqrstuvwxyz'): string
    {
        $str = '';
        for($i = 0; $i < $cnt; $i++) {
            if ($len == 0) $len = rand(4, 24);
            $str .= $this->createStr($len, $chars) . ' ';
        }
        return trim($str);
    }


    public function createLipsumStr(int $paragraphs = 2): string
    {
        $names = array(
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque venenatis id ex a hendrerit. Fusce dictum quis felis vel cursus. Proin sollicitudin sed justo ut accumsan. Integer tincidunt lacus nibh, quis viverra turpis ultrices eget. Donec in enim et nibh faucibus laoreet. Curabitur aliquam purus vitae luctus vestibulum. Donec facilisis augue vitae lorem gravida ornare. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.',
            'Fusce sed erat a odio eleifend iaculis vel non urna. Quisque consequat nunc quam, sit amet tincidunt dolor suscipit vel. Fusce id elit ligula. In ut augue purus. Aenean eu molestie ipsum. Sed porta eros quis efficitur euismod. Maecenas nec erat dictum, scelerisque elit at, ornare quam. Pellentesque et feugiat neque.'
        );
        $str = '';
        for($i = 0; $i < $paragraphs; $i++) {
            $str .= $names[rand(0, count($names)-1)] . "\n";
        }
        return $str;
    }

    public function createLipsumHtml(int $paragraphs = 2): string
    {
        $names = array(
            '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque venenatis id ex a hendrerit. Fusce dictum quis felis vel cursus. Proin sollicitudin sed justo ut accumsan. Integer tincidunt lacus nibh, quis viverra turpis ultrices eget. Donec in enim et nibh faucibus laoreet. Curabitur aliquam purus vitae luctus vestibulum. Donec facilisis augue vitae lorem gravida ornare. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.</p>',
            '<p>Fusce sed erat a odio eleifend iaculis vel non urna. Quisque consequat nunc quam, sit amet tincidunt dolor suscipit vel. Fusce id elit ligula. In ut augue purus. Aenean eu molestie ipsum. Sed porta eros quis efficitur euismod. Maecenas nec erat dictum, scelerisque elit at, ornare quam. Pellentesque et feugiat neque.</p>'
        );
        $str = '';
        for($i = 0; $i < $paragraphs; $i++) {
            $str .= $names[rand(0, count($names)-1)] . "\n";
        }
        return $str;
    }

    public function createColourString(): string
    {
        $names = [
            "AliceBlue", "AntiqueWhite", "Aqua", "Aquamarine", "Azure", "Beige", "Bisque", "Black", "BlanchedAlmond",
            "Blue", "BlueViolet", "Brown", "BurlyWood", "CadetBlue", "Chartreuse", "Chocolate", "Coral", "CornflowerBlue",
            "Cornsilk", "Crimson", "Cyan", "DarkBlue", "DarkCyan", "DarkGoldenRod", "DarkGray", "DarkGrey", "DarkGreen",
            "DarkKhaki", "DarkMagenta", "DarkOliveGreen", "DarkOrange", "DarkOrchid", "DarkRed", "DarkSalmon", "DarkSeaGreen",
            "DarkSlateBlue", "DarkSlateGray", "DarkSlateGrey", "DarkTurquoise", "DarkViolet", "DeepPink", "DeepSkyBlue",
            "DimGray", "DimGrey", "DodgerBlue", "FireBrick", "FloralWhite", "ForestGreen", "Fuchsia", "Gainsboro",
            "GhostWhite", "Gold", "GoldenRod", "Gray", "Grey", "Green", "GreenYellow", "HoneyDew", "HotPink", "IndianRed",
            "Indigo", "Ivory", "Khaki", "Lavender", "LavenderBlush", "LawnGreen", "LemonChiffon", "LightBlue", "LightCoral",
            "LightCyan", "LightGoldenRodYellow", "LightGray", "LightGrey", "LightGreen", "LightPink", "LightSalmon",
            "LightSeaGreen", "LightSkyBlue", "LightSlateGray", "LightSlateGrey", "LightSteelBlue", "LightYellow", "Lime",
            "LimeGreen", "Linen", "Magenta", "Maroon", "MediumAquaMarine", "MediumBlue", "MediumOrchid", "MediumPurple",
            "MediumSeaGreen", "MediumSlateBlue", "MediumSpringGreen", "MediumTurquoise", "MediumVioletRed", "MidnightBlue",
            "MintCream", "MistyRose", "Moccasin", "NavajoWhite", "Navy", "OldLace", "Olive", "OliveDrab", "Orange", "OrangeRed",
            "Orchid", "PaleGoldenRod", "PaleGreen", "PaleTurquoise", "PaleVioletRed", "PapayaWhip", "PeachPuff", "Peru",
            "Pink", "Plum", "PowderBlue", "Purple", "RebeccaPurple", "Red", "RosyBrown", "RoyalBlue", "SaddleBrown", "Salmon",
            "SandyBrown", "SeaGreen", "SeaShell", "Sienna", "Silver", "SkyBlue", "SlateBlue", "SlateGray", "SlateGrey",
            "Snow", "SpringGreen", "SteelBlue", "Tan", "Teal", "Thistle", "Tomato", "Turquoise", "Violet", "Wheat", "White",
            "WhiteSmoke", "Yellow", "YellowGreen",
        ];
        return $names[rand(0, count($names)-1)];
    }

    public function createSpecies(): string
    {
        $names = array('Dog', 'Goat', 'Pig', 'Sheep', 'Cattle', 'Cat', 'Chicken',
            'Guinea Pig', 'Donkey', 'Fish', 'Horse', 'Rabbit ', 'Bird', 'Snake', 'Possum', 'Kangaroo');
        return $names[rand(0, count($names)-1)];
    }

    public function createBreed(): string
    {
        $names = array('Affenpinscher', 'Afghan Hound', 'Airedale Terrier', 'Akita', 'Alaskan Klee Kai', 'Alaskan Malamute', 'American Bulldog',
            'American English Coonhound', 'American Eskimo Dog', 'American Foxhound', 'American Pit Bull Terrier', 'American Staffordshire Terrier',
            'American Water Spaniel', 'Anatolian Shepherd Dog', 'Appenzeller Sennenhunde', 'Australian Cattle Dog', 'Australian Kelpie',
            'Australian Shepherd', 'Australian Terrier', 'Azawakh', 'Barbet', 'Basenji', 'Basset Hound', 'Beagle', 'Bearded Collie', 'Bedlington Terrier',
            'Belgian Malinois', 'Belgian Sheepdog', 'Belgian Tervuren', 'Berger Picard', 'Bernedoodle', 'Bernese Mountain Dog', 'Bichon Frise',
            'Black and Tan Coonhound', 'Black Mouth Cur', 'Black Russian Terrier', 'Bloodhound', 'Blue Lacy', 'Bluetick Coonhound', 'Boerboel',
            'Bolognese', 'Border Collie', 'Border Terrier', 'Borzoi', 'Boston Terrier', 'Bouvier des Flandres', 'Boxer', 'Boykin Spaniel',
            'Bracco Italiano', 'Briard', 'Brittany', 'Brussels Griffon', 'Bull Terrier', 'Bulldog', 'Bullmastiff', 'Cairn Terrier', 'Canaan Dog',
            'Cane Corso', 'Cardigan Welsh Corgi', 'Catahoula Leopard Dog', 'Caucasian Shepherd Dog', 'Cavalier King Charles Spaniel', 'Cesky Terrier',
            'Chesapeake Bay Retriever', 'Chihuahua', 'Chinese Crested', 'Chinese Shar-Pei', 'Chinook', 'Chow Chow', 'Clumber Spaniel', 'Cockapoo',
            'Cocker Spaniel', 'Collie', 'Coton de Tulear', 'Curly-Coated Retriever', 'Dachshund', 'Dalmatian', 'Dandie Dinmont Terrier', 'Doberman Pinscher',
            'Dogo Argentino', 'Dogue de Bordeaux', 'Dutch Shepherd', 'English Cocker Spaniel', 'English Foxhound', 'English Setter',
            'English Springer Spaniel', 'English Toy Spaniel', 'Entlebucher Mountain Dog', 'Field Spaniel', 'Finnish Lapphund', 'Finnish Spitz',
            'Flat-Coated Retriever', 'Fox Terrier', 'French Bulldog', 'German Pinscher', 'German Shepherd Dog', 'German Shorthaired Pointer',
            'German Wirehaired Pointer', 'Giant Schnauzer', 'Glen of Imaal Terrier', 'Goldador', 'Golden Retriever', 'Goldendoodle', 'Gordon Setter',
            'Great Dane', 'Great Pyrenees', 'Greater Swiss Mountain Dog', 'Greyhound', 'Harrier', 'Havanese', 'Ibizan Hound', 'Icelandic Sheepdog',
            'Irish Red and White Setter', 'Irish Setter', 'Irish Terrier', 'Irish Water Spaniel', 'Irish Wolfhound', 'Italian Greyhound', 'Jack Russell Terrier',
            'Japanese Chin', 'Japanese Spitz', 'Karelian Bear Dog', 'Keeshond', 'Kerry Blue Terrier', 'Komondor', 'Kooikerhondje', 'Korean Jindo Dog', 'Kuvasz',
            'Labradoodle', 'Labrador Retriever', 'Lakeland Terrier', 'Lancashire Heeler', 'Leonberger', 'Lhasa Apso', 'Lowchen', 'Maltese', 'Maltese Shih Tzu',
            'Maltipoo', 'Manchester Terrier', 'Mastiff', 'Miniature Pinscher', 'Miniature Schnauzer', 'Mudi', 'Mutt', 'Neapolitan Mastiff', 'Newfoundland',
            'Norfolk Terrier', 'Norwegian Buhund', 'Norwegian Elkhound', 'Norwegian Lundehund', 'Norwich Terrier', 'Nova Scotia Duck Tolling Retriever',
            'Old English Sheepdog', 'Otterhound', 'Papillon', 'Peekapoo', 'Pekingese', 'Pembroke Welsh Corgi', 'Petit Basset Griffon Vendeen',
            'Pharaoh Hound', 'Plott', 'Pocket Beagle', 'Pointer', 'Polish Lowland Sheepdog', 'Pomeranian', 'Pomsky', 'Poodle', 'Portuguese Water Dog',
            'Pug', 'Puggle', 'Puli', 'Pyrenean Shepherd', 'Rat Terrier', 'Redbone Coonhound', 'Rhodesian Ridgeback', 'Rottweiler', 'Saint Bernard',
            'Saluki', 'Samoyed', 'Schipperke', 'Schnoodle', 'Scottish Deerhound', 'Scottish Terrier', 'Sealyham Terrier', 'Shetland Sheepdog', 'Shiba Inu',
            'Shih Tzu', 'Siberian Husky', 'Silky Terrier', 'Skye Terrier', 'Sloughi', 'Small Munsterlander Pointer', 'Soft Coated Wheaten Terrier', 'Stabyhoun',
            'Staffordshire Bull Terrier', 'Standard Schnauzer', 'Sussex Spaniel', 'Swedish Vallhund', 'Tibetan Mastiff', 'Tibetan Spaniel', 'Tibetan Terrier',
            'Toy Fox Terrier', 'Treeing Tennessee Brindle', 'Treeing Walker Coonhound', 'Vizsla', 'Weimaraner', 'Welsh Springer Spaniel', 'Welsh Terrier',
            'West Highland White Terrier', 'Whippet', 'Wirehaired Pointing Griffon', 'Xoloitzcuintli', 'Yorkipoo', 'Yorkshire Terrier');
        return $names[rand(0, count($names)-1)];
    }

    public function createActivityName(): string
    {
        $names = array(
            'Ultrasound CE workshop',
            'Ultrasound DVM teaching',
            'Neurology Practical',
            'Cardiology - ECG Practical',
            'Reproduction Practical',
            'Health Check - Routine Care',
            'Exercise / Walking',
            'Foster stay',
            'Gastrointestinal Practical',
            'Ophthalmology Practical',
            'Dermatology Practical',
            'Communications Practical'
        );
        return $names[rand(0, count($names)-1)];
    }

    public function createRandomDate(\DateTime $from = null, \DateTime $to = null): \DateTime
    {
        if (!$from) {
            $from = \Tk\Date::create(strtotime('10 September 2000'));
        }
        if (!$to) {
            $to = \Tk\Date::create();   // Now
        }
        $ts = mt_rand($from->getTimestamp(), $to->getTimestamp());
        return \Tk\Date::create($ts);
    }

}
