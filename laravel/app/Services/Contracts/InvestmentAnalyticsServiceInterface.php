<?php

namespace App\Services\Contracts;

interface InvestmentAnalyticsServiceInterface
{
    /**
     * Get the average age across all investors.
     *
     * @return float
     */
    public function getAverageInvestorAge(): float;

    /**
     * Get the average investment amount across all investors/investments.
     *
     * @return float
     */
    public function getAverageInvestmentAmount(): float;

    /**
     * Get the total number of investments.
     *
     * @return int
     */
    public function getTotalInvestmentsCount(): int;
}
