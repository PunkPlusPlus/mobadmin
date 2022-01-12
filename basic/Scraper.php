<?php

namespace app\basic;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Symfony\Component\DomCrawler\Crawler;
use Raulr\GooglePlayScraper\Exception\RequestException;
use Raulr\GooglePlayScraper\Exception\NotFoundException;
use app\controllers\ProxyController;
use app\basic\debugHelper;

class Scraper extends \Raulr\GooglePlayScraper\Scraper
{
    protected $proxyIp = "";

    public function setProxy($proxyIp)
    {
        $this->proxyIp = $proxyIp;
    }

    protected function request($path, array $params = array())
    {
        // handle delay
        if (!empty($this->delay) && !empty($this->lastRequestTime)) {
            $currentTime = microtime(true);
            $delaySecs = $this->delay / 1000;
            $delay = max(0, $delaySecs - $currentTime + $this->lastRequestTime);
            usleep($delay * 1000000);
        }
        $this->lastRequestTime = microtime(true);

        if (is_array($path)) {
            $path = implode('/', $path);
        }
        $path = ltrim($path, '/');
        $path = rtrim('/store/'.$path, '/');
        $url = self::BASE_URL.$path;
        $query = http_build_query($params);
        if ($query) {
            $url .= '?'.$query;
        }
        //debugHelper::print($url);
        //$crawler = $this->client->request('GET', $url);
        $this->client->getClient()->setProxy($this->proxyIp);
        $crawler = $this->client->request('GET', $url);
        //$crawler = $this->client->request('GET', "https://2ip.ru");
        //debugHelper::print($crawler);
        $status_code = $this->client->getResponse()->getStatus();
        if ($status_code == 404) {
            throw new NotFoundException('Requested resource not found');
        } elseif ($status_code != 200) {
            throw new RequestException(sprintf('Request failed with "%d" status code', $status_code), $status_code);
        }

        return $crawler;
    }
}