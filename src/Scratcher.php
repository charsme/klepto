<?php
namespace Klepto;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;
use Psr\Http\Message\UriInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\TransferStats;
use Klepto\Traits\UrifyStr;
use Klepto\Traits\FormatBytes;
use GuzzleHttp\Exception\SeekException;

/**
 * Scratcher Class
 */
class Scratcher implements LoggerAwareInterface
{
    use LoggerAwareTrait, UrifyStr, FormatBytes;
    private $client;

    /**
     * Constructor
     *
     * @Inject
     * @param Client $client
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger, Client $client)
    {
        $this->client = $client;
        $this->setLogger($logger);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function __invoke(
        $uri,
        callable $next,
        array $conf = []
    ) {
        try {
            $response = $this->client->request(
                'GET',
                $uri,
                $this->assign($conf)
            );
            
            if ($response->getStatusCode() < 400) {
                return $next(
                    $response
                );
            }
            
            $this->logger->info(
                (string) $uri . " got status => " . $response->getStatusCode()
            );

            return $response->getStatusCode();
        } catch (GuzzleException $exception) {
            return $this->handle($exception, $conf);
        }
    }

    private function assign(
        array $conf
    ):array {
        if (isset($conf['sink'])) {
            $this->ensureStorage(
                dirname(
                    $conf['sink']
                )
            );
        }

        if (isset($conf['storage'])) {
            $conf['sink'] = $this->ensureStorage(
                $conf['storage']['dir']
            ) . '/' . $this->urifyStr(
                $conf['storage']['name']
            ) . '.' . $conf['storage']['extension'];
        }

        return array_merge(
            [
                'on_stats' => function (TransferStats $stats) {
                    $this->logger->info(
                        (string) $stats->getEffectiveUri(),
                        [
                            'status' => $stats->hasResponse() ? $stats->getResponse()->getStatusCode() : 'null',
                            'transfer-time' => $stats->getTransferTime(),
                            'error' => $stats->getHandlerErrorData(),
                            'size' => $this->formatBytes(
                                (int) $stats->getHandlerStat('size_download')
                            ),
                            'speed' => $this->formatBytes(
                                (int) $stats->getHandlerStat('speed_download')
                            )
                        ]
                    );
                }
            ],
            $conf
        );
    }

    private function ensureStorage(string $dir)
    {
        if (!\file_exists($dir)) {
            \mkdir($dir, 0755, true);
        }
        
        return $dir;
    }

    private function handle(
        GuzzleException $exception,
        array $conf = []
    ) {
        $this->logger->warning((string) $exception);

        if ($exception instanceof RequestException) {
            if ($exception->hasResponse()) {
                $this->logger->info(
                    $exception->getResponse()->getStatusCode() .
                    " => " .
                    (string) $exception->getRequest()->getUri()
                );
            }
        }

        if ($exception instanceof SeekException) {
            if (isset($conf['sink'])) {
                return $this->delete($conf['sink']);
            }
        }
    }

    private function delete($sink)
    {
        if (\file_exists($sink)) {
            return unlink($sink);
        }

        return false;
    }
}
