<?php

namespace lfg;

use yii\log\Logger;
use yii\log\Target;

/**
 * Datadog Log Target
 * Send errors and warnings count to Datadog
 *
 * Class DatadogTarget
 * @package app\components
 */
class DatadogCounter extends Target
{

    /**
     * Defaults levels to log
     * @var array
     */
    public $levels = ['error'];
    /**
     * Prefix for metric name (required)
     * @var string
     */
    public $metricPrefix;

    /**
     * Tags, merged with message category (optional)
     * @var array
     */
    public $tags = [];

    /**
     * Ignored categories. Defaults to ['yii\web\HttpException:404']
     * @var array
     */
    public $ignoredCategories = ['yii\web\HttpException:404'];

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        if ($this->metricPrefix !== null) {
            foreach ($this->messages as $message) {
                if (in_array($message[2], $this->ignoredCategories, true)) {
                    continue;
                }
                \Datadogstatsd::increment(
                    $this->metricPrefix . '.' . Logger::getLevelName($message[1]),
                    1.0,
                    array_merge($this->tags, [$message[2]])
                );
            }
        }
    }
}