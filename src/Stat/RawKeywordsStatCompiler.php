<?php

namespace Adimeo\DataSuite\Stat;

use Adimeo\DataSuite\Index\StatIndexManager;

class RawKeywordsStatCompiler extends StatCompiler
{
    public function getDisplayName()
    {
        return "Keywords (raw) statistics";
    }

    public function compile($mapping, $from, $to, $period)
    {
        $query = '
      {
      "query": {
          "bool": {
              "must": [{
                "term": {
                  "mapping": "'.$mapping.'"
                }
              }]
          }
      },
      "aggs": {
          "keywords_raw": {
            "terms": {
                "field": "keywords_raw",
                "size": 20
            }
         }
      }
    }';
        $query = json_decode($query, TRUE);
        if ($from != null) {
            $query['query']['bool']['must'][] = json_decode('{
                    "range": {
                        "date": {
                            "gte": "'.$from->format('Y-m-d\TH:i').'"
                        }
                    }
                }', TRUE);
        }
        if ($to != null) {
            $query['query']['bool']['must'][] = json_decode('{
                    "range": {
                        "date": {
                            "lte": "'.$to->format('Y-m-d\TH:i').'"
                        }
                    }
                }', TRUE);
        }

        $res = $this->getStatIndexManager()->search(StatIndexManager::APP_INDEX_NAME, $query, 0, 0, 'stat');

        if (isset($res['aggregations']['keywords_raw']['buckets'])) {
            $data = [];
            foreach ($res['aggregations']['keywords_raw']['buckets'] as $bucket) {
                $data[] = [
                    $bucket['key'],
                    $bucket['doc_count']
                ];
            }
            $this->setData($data);
        }
    }

    public function getHeaders()
    {
        return ['Keywords', 'Count'];
    }

    public function getGoogleChartClass()
    {
        return 'google.visualization.ColumnChart';
    }

    public function getJSData()
    {
        $js = 'var statData = new google.visualization.DataTable();
    statData.addColumn("string", "Keywords");
    statData.addColumn("number", "Count");

    statData.addRows([';

        $first = true;
        //Data
        foreach ($this->getData() as $data) {
            if ($data[0] != null && !empty($data[0]) && $data[1] != null && !empty($data[1])) {
                if (!$first)
                    $js .= ',';
                $first = false;
                $js .= '["'.$data[0].'", '.$data[1].']';
            }
        }

        $js .= ']);';

        $js .= 'var chartOptions = {
          title: "Keywords statistics",
          legend: { position: "bottom" }
        };';
        return $js;
    }
}
