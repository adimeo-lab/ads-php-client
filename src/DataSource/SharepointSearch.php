<?php

namespace Adimeo\DataSuite\DataSource;

use Adimeo\DataSuite\Model\DataSource;
use Office365\PHP\Client\Runtime\Auth\AuthenticationContext;
use Office365\PHP\Client\Runtime\Utilities\RequestOptions;
use Office365\PHP\Client\SharePoint\ClientContext;

class SharepointSearch extends DataSource
{
    const SHAREPOINT_PAGER_SIZE = 500;

    private $authContext = null;

    private $globalCount = 0;

    public function getDisplayName(): string
    {
        return 'Sharepoint search';
    }

    public function getOutputFields(): array
    {
        return [
            'authContext',
            'docId',
            'docPath',
            'relativePath',
            'siteName',
            'uniqueId',
            'properties'
        ];
    }

    public function getSettingFields(): array
    {
        return [
            'company_url' => [
                'type' => 'text',
                'label' => 'Company url (E.g.: https://mycompany.sharepoint.com)',
                'required' => true
            ],
            'username' => [
                'type' => 'text',
                'label' => 'Username',
                'required' => true
            ],
            'password' => [
                'type' => 'text',
                'label' => 'Password',
                'required' => true
            ],
            'search_request' => [
                'type' => 'text',
                'label' => 'Sharepoint search request (See doc https://docs.microsoft.com/fr-fr/sharepoint/dev/general-development/keyword-query-language-kql-syntax-reference)',
                'required' => true
            ],
            'select_properties' => [
                'type' => 'text',
                'label' => 'Select properties (comma separated)',
                'required' => false
            ]
        ];
    }

    public function getExecutionArgumentFields(): array
    {
        return [
            'search_request' => [
                'type' => 'text',
                'label' => 'Sharepoint search request (See doc https://docs.microsoft.com/fr-fr/sharepoint/dev/general-development/keyword-query-language-kql-syntax-reference)',
                'required' => true,
                'default_from_settings' => true
            ]
        ];
    }

    public function execute(array $args = [])
    {
        $this->querySharepoint($args['search_request']);
        $this->getOutputManager()->writeLn('Found '.$this->globalCount.' documents');
    }

    private function querySharepoint($searchRequest, $from = 0)
    {
        if ($this->authContext == null) {
            $this->authContext = new AuthenticationContext($this->getSettings()['company_url']);
            $this->authContext->acquireTokenForUser($this->getSettings()['username'], $this->getSettings()['password']);
        }

        $selectProperties = ['Path', 'LastModifiedTime', 'SiteName', 'UniqueId'];
        if (isset($this->getSettings()['select_properties']) && !empty($this->getSettings()['select_properties'])) {
            foreach (array_map('trim', explode(',', $this->getSettings()['select_properties'])) as $prop) {
                if (!in_array($prop, $selectProperties)) {
                    $selectProperties[] = $prop;
                }
            }
        }

        $searchQuery = "'(".$searchRequest.") AND IsDocument:true'";

        $searchUrl = trim($this->getSettings()['company_url'], '/')
            ."/_api/search/query?"
            ."querytext=".rawurlencode($searchQuery)
            ."&selectproperties=".rawurlencode("'".implode(',', $selectProperties)."'")
            ."&sortlist=".rawurlencode("'LastModifiedTime:descending'")
            ."&rowlimit=".static::SHAREPOINT_PAGER_SIZE
            ."&startrow=".$from;

        $request = new RequestOptions($searchUrl);
        $ctx = new ClientContext($searchUrl, $this->authContext);
        $data = $ctx->executeQueryDirect($request);

        $data = json_decode($data, TRUE);
        $count = 0;
        foreach ($data['d']['query']['PrimaryQueryResult']['RelevantResults']['Table']['Rows']['results'] as $row) {
            $doc = [];
            foreach ($row['Cells']['results'] as $cell) {
                if (isset($cell['Key']) && isset($cell['Value'])) {
                    $doc[$cell['Key']] = $cell['Value'];
                }
            }
            $to_index = [
                'authContext' => $this->authContext,
                'docId' => isset($doc['DocId']) ? $doc['DocId'] : null,
                'docPath' => isset($doc['Path']) ? $doc['Path'] : null,
                'relativePath' => null,
                'siteName' => isset($doc['SiteName']) ? $doc['SiteName'] : null,
                'uniqueId' => isset($doc['UniqueId']) ? strtolower(trim($doc['UniqueId'], '{}')) : null,
                'properties' => $doc
            ];
            if (isset($doc['Path'])) {
                $tmp_r = explode('//', $doc['Path']);
                $tmp_rr = explode('/', $tmp_r[1]);
                $tmp_rr = array_slice($tmp_rr, 1);
                $relativePath = '/'.implode('/', $tmp_rr);
                $to_index['relativePath'] = $relativePath;
            }
            $this->index($to_index);
            $count++;
            $this->globalCount++;
        }
        if ($count > 0) {
            $this->querySharepoint($searchRequest, $from + static::SHAREPOINT_PAGER_SIZE);
        }
    }
}
