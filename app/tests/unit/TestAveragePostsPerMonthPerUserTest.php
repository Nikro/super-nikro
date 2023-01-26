<?php

declare(strict_types = 1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use SocialPost\Hydrator\FictionalPostHydrator;
use Statistics\Dto\ParamsTo;
use Statistics\Enum\StatsEnum;
use Statistics\Service\Factory\StatisticsServiceFactory;

/**
 * Class TestAveragePostsPerMonthPerUserTest
 *
 * @package Tests\unit
 */
class TestAveragePostsPerMonthPerUserTest extends TestCase
{
    /**
     * @test
     */
    public function testNothing(): void
    {
        // Load the json data.
        $this->assertFileExists(__DIR__ . '/../data/social-posts-response.json');
        $json = file_get_contents(__DIR__ . '/../data/social-posts-response.json');
        $data = json_decode($json);

        // Array to Traversable.
        $hydratedPosts = new \ArrayIterator();
        $hydrator = new FictionalPostHydrator();
        foreach ($data->data->posts as $key => $post) {
            $hydratedPosts->append($hydrator->hydrate((array) $post));
        }
        $this->assertIsIterable($hydratedPosts);

        // Prepare the params.
        $params = [
            (new ParamsTo())
                ->setStatName(StatsEnum::AVERAGE_POSTS_NUMBER_PER_USER_PER_MONTH)
                ->setStartDate(new \DateTime('2017-01-01'))
                ->setEndDate(new \DateTime('2019-01-01')),
        ];
        $statsService = StatisticsServiceFactory::create();
        $output = $statsService->calculateStats($hydratedPosts, $params);
        $this->assertNotEmpty($output);

        // Make the checks that we're sure of.
        $this->assertIsString($output->getChildren()[0]->getChildren()[0]->getSplitPeriod());
        $this->assertTrue($output->getChildren()[0]->getChildren()[0]->getSplitPeriod() === 'August of 2018');

        $this->assertIsFloat($output->getChildren()[0]->getChildren()[0]->getValue());
        $this->assertTrue($output->getChildren()[0]->getChildren()[0]->getValue() === 1.25);
        $this->assertPreConditions();
    }
}
