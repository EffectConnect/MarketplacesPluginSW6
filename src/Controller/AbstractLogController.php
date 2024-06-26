<?php declare(strict_types=1);

/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EffectConnect\Marketplaces\Controller;

use DateTime;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Shopware\Core\Framework\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use ZipArchive;

class AbstractLogController extends AbstractController
{
    private const PLUGIN_DIR = '../custom/plugins/EffectConnectMarketplaces';
    private const LOG_DIR = self::PLUGIN_DIR . '/data/logs';

    #[Route(path: '/getAll', name: 'log_get_all', methods: ['GET'])]
    public function getLogFiles(Request $request, Context $context): JsonResponse
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(self::LOG_DIR), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $path) {
            if ($path->isDir()) {
                continue;
            }
            $fullPath = $path->__toString();
            $file = new \stdClass();
            $file->filename = basename($fullPath);
            $file->path = '.';
            $file->lastUpdatedAt = filectime($fullPath);
            $files[] = $file;
        }

        usort($files, function ($file1, $file2) {return $file1->lastUpdatedAt < $file2->lastUpdatedAt;});

        return new JsonResponse(['files' => $files]);
    }

    #[Route(path: '/downloadFiles', name: 'download_files', methods: ['GET'])]
    public function downloadLogFiles(Request $request, Context $context): JsonResponse
    {
        $filenames = explode(',', $request->get('filenames'));
        $single = count($filenames) === 1;

        if ($single) {
            $filename = implode('/', [self::LOG_DIR, $filenames[0]]);
        } else {
            $filename = $this->zipFiles($filenames);
        }
        $file = $this->file($filename);
        $file->send();
        $file->deleteFileAfterSend(!$single);
        die();
    }

    private function zipFiles(array $filenames): string
    {
        $zip = new ZipArchive();
        $dir = self::PLUGIN_DIR . '/data';
        $tmpFile = $dir . '/' . (new DateTime())->getTimestamp(). '.zip';
        $zip->open($tmpFile, ZipArchive::CREATE);
        foreach($filenames as $file) {
            $filepath = implode('/', [self::LOG_DIR, $file]);
            $zip->addFile($filepath, basename($file));
        }
        $zip->close();
        return $tmpFile;
    }

}