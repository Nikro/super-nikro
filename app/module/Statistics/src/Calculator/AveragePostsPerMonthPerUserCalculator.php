<?php

declare(strict_types = 1);

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

class AveragePostsPerMonthPerUserCalculator extends AbstractCalculator
{
    /**
     * @var string The units of the output.
     */
    protected const UNITS = 'posts per user';

    /**
     * @var array $usersMonthlyAverage
     *
     * Contains an array, key - month-year combination, value - array of users and their total posts count.
     */
    private array $usersMonthlyAverage = [];

    /**
     * @inheritDoc
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        // Compose a human-readable key for the user (to be used in the output).
        $userId = $postTo->getAuthorId();
        $userName = $postTo->getAuthorName();
        $userKeyComposite = sprintf('%s (%d)', $userName, $userId);

        // Do the same for the month (month-year combination).
        $monthYear = $postTo->getDate()->format('F \o\f Y');
        $this->usersMonthlyAverage[$monthYear][$userKeyComposite] = ($this->usersMonthlyAverage[$monthYear][$userKeyComposite] ?? 0) + 1;
    }

    /**
     * @inheritDoc
     */
    protected function doCalculate(): StatisticsTo
    {
        // Calculate the average posts per month per user.
        $this->usersMonthlyAverage = array_map(function ($monthYear) {
            $total = array_sum($monthYear);
            $count = count($monthYear);
            return $total / $count;
        }, $this->usersMonthlyAverage);

        // Create the output (similar to the TotalPostsPerWeek calculator).
        $stats = new StatisticsTo();
        foreach ($this->usersMonthlyAverage as $monthYear => $average) {
            $child = (new StatisticsTo())
                ->setName($this->parameters->getStatName())
                ->setSplitPeriod($monthYear)
                ->setValue($average)
                ->setUnits(self::UNITS);

            $stats->addChild($child);
        }

        return $stats;
    }
}
