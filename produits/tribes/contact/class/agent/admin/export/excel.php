<?php

class agent_admin_export_excel extends agent
{
    const contentType = 'application/vnd.ms-excel';

    protected

    $requiredAuth = 'admin',
    $sql = 'SELECT * FROM contact_contact c WHERE NOT is_obsolete';


    function compose($o)
    {
        $db = DB();

        $xls = new Spreadsheet_Excel_Writer();
        $xls->setVersion(8);
        $xls->send($CONFIG['tribes.emailDomain'] . '-' . date('Y-m-d-His', $_SERVER['REQUEST_TIME']) . '.xls');

        $sheet = $xls->addWorksheet('Contacts');
        $sheet->setInputEncoding('UTF-8');

        $headFormat = $xls->addFormat();
        $headFormat->setBold();

        $xlsRow = 1;
        $result = $db->query($this->sql);

        while ($row = $result->fetchRow())
        {
            if ($headFormat)
            {
                $xlsCol = 0;
                foreach ($row as $k => $v) $sheet->write(0, $xlsCol++, $k, $headFormat);
                $headFormat = false;

                $sheet->freezePanes(array(1, 0));
            }

            $xlsCol = 0;
            foreach ($row as $v)
            {
                switch ($v)
                {
                case '0000-00-00':
                case '0000-00-00 00:00:00': break;
                default: $sheet->write($xlsRow, $xlsCol, $v);
                }

                ++$xlsCol;
            }

            ++$xlsRow;
        }

        $xls->close();

        return $o;
    }
}
