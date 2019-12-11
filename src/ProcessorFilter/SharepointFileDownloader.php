<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;
use Office365\PHP\Client\Runtime\Auth\AuthenticationContext;
use Office365\PHP\Client\Runtime\Utilities\RequestOptions;
use Office365\PHP\Client\SharePoint\ClientContext;

class SharepointFileDownloader extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "Sharepoint file downloader";
    }

    public function getSettingFields()
    {
        return [];
    }

    public function getFields()
    {
        return ['filePath'];
    }

    public function getArguments()
    {
        return [
            'authContext' => 'Authentication context',
            'siteName' => 'Site name',
            'relativePath' => 'Relative path'
        ];
    }

    public function execute(&$document, DataSource $datasource)
    {
        /** @var AuthenticationContext $authCtx */
        $authCtx = $this->getArgumentValue('authContext', $document);

        $path_r = explode('//', $this->getArgumentValue('siteName', $document));
        $companyUrl = 'https://'.explode('/', $path_r[1])[0];

        $downloadUrl = $this->getArgumentValue('siteName', $document)."/_api/web/GetFileByServerRelativeUrl('".rawurlencode($this->getArgumentValue('relativePath', $document))."')/\$value?@target='".urlencode($companyUrl)."'";
        $fileRequest = new RequestOptions($downloadUrl);
        $ctxFile = new ClientContext($downloadUrl, $authCtx);
        $content = $ctxFile->executeQueryDirect($fileRequest);
        $tempFile = tempnam(sys_get_temp_dir(), 'ads_sp_');
        $datasource->getOutputManager()->writeLn('>>> Downloading file '.$this->getArgumentValue('relativePath', $document));
        file_put_contents($tempFile, $content);

        return [
            'filePath' => $tempFile
        ];
    }
}
