<?php

namespace Adimeo\DataSuite\ProcessorFilter;

use Adimeo\DataSuite\Model\DataSource;
use Adimeo\DataSuite\Model\ProcessorFilter;

class DeleteDocumentFilter extends ProcessorFilter
{
    public function getDisplayName()
    {
        return "Delete document filter";
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
            ],
            'mapping_name' => [
                'label' => 'Mapping name',
                'type' => 'string',
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
            $datasource->getExecIndexManager()->getClient()->delete([
                'index' => $this->getSettings()['index_name'],
                'type' => $this->getSettings()['mapping_name'],
                'id' => $this->getArgumentValue('doc_id', $document),
            ]);
            $datasource->getExecIndexManager()->getClient()->indices()->flush();
        } catch (\Exception $ex) {
            $datasource->getOutputManager()->writeLn('Exception ==> '.$ex->getMessage());
        }
        $document = [];
        return ['doc' => NULL];
    }
}
