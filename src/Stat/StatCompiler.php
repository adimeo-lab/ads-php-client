<?php

namespace Adimeo\DataSuite\Stat;

use Adimeo\DataSuite\Index\StatIndexManager;

abstract class StatCompiler
{
    public const STAT_PERIOD_MIN = 1;
    public const STAT_PERIOD_HOUR = 2;
    public const STAT_PERIOD_DAY = 3;
    public const STAT_PERIOD_WEEK = 4;
    public const STAT_PERIOD_MONTH = 5;
    public const STAT_PERIOD_YEAR = 6;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var StatIndexManager
     */
    private $statIndexManager;

    public function __construct(StatIndexManager $statIndexManager)
    {
        $this->statIndexManager = $statIndexManager;
    }

    /**
     * @return StatIndexManager
     */
    public function getStatIndexManager()
    {
        return $this->statIndexManager;
    }

    /**
     * @param StatIndexManager $statIndexManager
     */
    public function setStatIndexManager($statIndexManager)
    {
        $this->statIndexManager = $statIndexManager;
    }

    /**
     * @return string
     */
    abstract function getDisplayName();

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @param integer $period
     */
    abstract function compile($mapping, $from, $to, $period);

    /**
     * @return mixed
     */
    abstract function getHeaders();

    /**
     * @return string
     */
    abstract function getGoogleChartClass();

    /**
     * @return string
     */
    abstract function getJSData();

    /**
     * @param integer $period
     *
     * @return string
     */
    public function getElasticPeriod($period)
    {
        switch ($period) {
            case StatCompiler::STAT_PERIOD_MIN:
                return "minute";
            case StatCompiler::STAT_PERIOD_HOUR:
                return "hour";
            case StatCompiler::STAT_PERIOD_DAY:
                return "day";
            case StatCompiler::STAT_PERIOD_WEEK:
                return "week";
            case StatCompiler::STAT_PERIOD_MONTH:
                return "month";
            case StatCompiler::STAT_PERIOD_YEAR:
                return "year";
        }
        return "year";
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    protected function setData($data)
    {
        $this->data = $data;
    }
}
