<?php

namespace SociallymapConnect\Controllers;

use SociallymapConnect\Enums\Log\LogLevel;
use SociallymapConnect\Includes\Logger;

class LogController extends BaseController
{
    /**
     * @param array $logs
     * @return array
     */
    private function flattenData(array $logs)
    {
        $data = [];

        $data['header'] =
            [
                'Date',
                'Id',
                'Level',
                'Message',
            ];

        foreach ($logs as $log) {
            $data['values'][] =
                [
                    $log['log_date'],
                    $log['id'],
                    $log['log_level'],
                    $log['log_message'],
                ];
        }

        return $data;
    }

    /**
     * @return mixed
     */
    private function getLogs()
    {
        return Logger::findAll();
    }

    public function logsDownload()
    {
        $date = new \DateTime();
        Logger::logInfo(sprintf('Logs download on %s', $date->format('d/m/Y')));

        $logs = $this->getLogs();
        $data = $this->flattenData($logs);

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="logsExport.csv"');

        $csv = fopen('php://output', 'wb');

        fputcsv($csv, $data['header']);

        foreach ($data['values'] as $line) {
            fputcsv($csv, $line);
        }

        exit;
    }

    /**
     * @return string
     */
    public function logsManager()
    {
        $currentPage = isset($_REQUEST['currentPage']) ? (int) $_REQUEST['currentPage'] : 1;
        $criteria = [];
        $order = [
            'log_date' => 'desc',
            'id'       => 'desc',
            ];
        $maxPerPage = 25;

        $log = [];

        $log['log_level'] = isset($_GET['filter']) ? $_GET['filter'] : '';

        $viewData = [
            'totalCount' => 0,
            'errorsCount' => 0,
            'infosCount' => 0,
            'messagesCount' => 0,
            'maxPerPage' => $maxPerPage,
            'filter' => $log['log_level'],
            'logsList' => []
        ];

        if (\array_key_exists('orderBy', $_GET)) {
            $order = $_GET['order'];
        }

        $logData = Logger::findBy($criteria, $order);
        $viewData['totalCount'] = count($logData);
        $listData = [];
        foreach ($logData as $log) {
            switch ($log['log_level']) {
                case LogLevel::ERROR :
                    $viewData['errorsCount'] ++;
                    break;
                case LogLevel::INFO :
                    $viewData['infosCount'] ++;
                    break;
                case LogLevel::MESSAGE_RECEIVED :
                    $viewData['messagesCount'] ++;
                    break;
            }

            $filterOk = isset($_GET['filter']) ? ($log['log_level'] === $_GET['filter']) : true;
            if ($filterOk) {
                $listData[] = $log;
            }
        }

        $offset = ($currentPage - 1) * $maxPerPage;
        $viewData['logsList'] = array_slice($listData, $offset, $maxPerPage);

        return self::render('logs-manager', $viewData);
    }
}
