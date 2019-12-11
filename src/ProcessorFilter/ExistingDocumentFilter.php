<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;

class ExistingDocumentFilter extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "Existing document finder";
    }

    public function getFields()
    {
        return ['doc'];
    }

    public function getSettingFields()
    {
        return [
            'index_name' => [
                'label' => 'Index name',
                'type' => 'choice',
                'bound_to' => 'index',
                'required' => true
            ]
        ];
    }

    public function getArguments()
    {
        return [
            'doc_id' => 'Document ID',
        ];
    }

    public function execute(&$document, DataSource $datasource)
    {
        try {
            $docId = $this->getArgumentValue('doc_id', $document);
            if (empty($docId))
                return ['doc' => null];
            $json = '{
          "query": {
              "ids": {"values":["'.(is_array($docId) ? implode('", "', $docId) : $docId).'"]}
          }
      }';
            $res = $datasource->getExecIndexManager()->search($this->getSettings()['index_name'], json_decode($json, TRUE));
            if (isset($res['hits']['hits'][0])) {
                return is_array($docId) ? ['doc' => $res['hits']['hits']] : ['doc' => $res['hits']['hits'][0]];
            }
        } catch (\Exception $ex) {
            $datasource->getOutputManager()->writeLn('Exception ==> '.$ex->getMessage());
        }
        return ['doc' => NULL];
    }
}
