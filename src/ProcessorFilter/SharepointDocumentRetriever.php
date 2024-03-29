<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;
use Office365\PHP\Client\Runtime\Auth\AuthenticationContext;
use Office365\PHP\Client\Runtime\Utilities\RequestOptions;
use Office365\PHP\Client\SharePoint\ClientContext;

class SharepointDocumentRetriever extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "Sharepoint document retriever";
    }

    public function getSettingFields()
    {
        return [
            'docs_only' => [
                'type' => 'boolean',
                'label' => 'Retrieve documents only (Stops processor otherwise)',
                'required' => false
            ]
        ];
    }

    public function getFields()
    {
        return ['doc'];
    }

    public function getArguments()
    {
        return [
            'authContext' => 'Authentication context',
            'siteName' => 'Site name',
            'uniqueId' => 'Element unique ID'
        ];
    }

    public function execute(&$document, DataSource $datasource)
    {
        /** @var AuthenticationContext $authCtx */
        $authCtx = $this->getArgumentValue('authContext', $document);

        $path_r = explode('//', $this->getArgumentValue('siteName', $document));
        $companyUrl = 'https://'.explode('/', $path_r[1])[0];

        $uniqueId = $this->getArgumentValue('uniqueId', $document);
        return [
            'doc' => $this->searchForDocument($authCtx, $companyUrl, $uniqueId)
        ];
    }

    private function searchForDocument(AuthenticationContext $authContext, $companyUrl, $uniqueId)
    {
        $searchQuery = "'UniqueId:\"{".$uniqueId."}\" AND IsDocument:true'";

        $searchUrl = trim($companyUrl, '/')
            ."/_api/search/query?"
            ."querytext=".rawurlencode($searchQuery);

        $request = new RequestOptions($searchUrl);
        $ctx = new ClientContext($searchUrl, $authContext);
        $data = $ctx->executeQueryDirect($request);

        $data = json_decode($data, TRUE);
        if (isset($data['d']['query']['PrimaryQueryResult']['RelevantResults']['Table']['Rows']['results'][0])) {
            $doc = [];
            $row = $data['d']['query']['PrimaryQueryResult']['RelevantResults']['Table']['Rows']['results'][0];
            foreach ($row['Cells']['results'] as $cell) {
                if (isset($cell['Key']) && isset($cell['Value'])) {
                    $doc[$cell['Key']] = $cell['Value'];
                }
            }
            if (isset($doc['Path'])) {
                $tmp_r = explode('//', $doc['Path']);
                $tmp_rr = explode('/', $tmp_r[1]);
                $tmp_rr = array_slice($tmp_rr, 1);
                $relativePath = '/'.implode('/', $tmp_rr);
                $doc['relativePath'] = $relativePath;
            }
            return $doc;
        }

        return null;
    }
}
