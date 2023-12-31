<?php

namespace App\DataFixtures;

use App\Entity\Profile;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ProfileFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $profile = new Profile();
        $profile->setRs('Facebook');
        $profile->setUrl('https://www.facebook.com/aymen.sellaouti');

        $profile = new Profile();
        $profile->setRs('twitter');
        $profile->setUrl('https://twitter.com/aymensellaouti');

        $profile1 = new Profile();
        $profile1->setRs('Facebook');
        $profile1->setUrl('https://www.facebook.com/aymen.sellaouti');

        $profile2 = new Profile();
        $profile2->setRs('LinkedIn');
        $profile2->setUrl('https://www.linkedin.com/in/aymen-sellaouti-b0427731/');

        $profile3 = new Profile();
        $profile3->setRs('Github');
        $profile3->setUrl('https://github.com/aymensellaouti');

        $manager->persist($profile);
        $manager->persist($profile2);
        $manager->persist($profile1);
        $manager->persist($profile3);

        $manager->flush();
    }
}
