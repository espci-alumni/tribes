<?php

class agent_admin_export_excel extends agent_admin_export
{
    public $contentType = 'application/vnd.ms-excel';

    protected $sheet, $head, $extension = '.xls';

    function compose($o)
    {
        $book = new Spreadsheet_Excel_Writer($this->tmp);
        $book->setVersion(8);

        $sheet = $this->sheet = $book->addWorksheet('Contacts');
        $sheet->setInputEncoding('UTF-8');
        $this->head = $book->addFormat();
        $this->head->setBold();

        $o = parent::compose($o);

        $book->close();

        return $o;
    }

    protected function mapRow($row, $count)
    {
        if (0 === $count)
        {
            $h = array();
            $col = 0;
            foreach ($row as $k => $v)
            {
                $this->sheet->write(0, $h[$k] = $col++, $k, $this->head);
            }

            $this->sheet->freezePanes(array(1, 0, 1, 0));
            $this->head = $h;
        }

        foreach ($row as $k => $v)
        {
            if (isset($this->head[$k])) $this->sheet->write($count+1, $this->head[$k], $v);
            else user_error("Key '{$k}' not found in headers names");
        }
    }
}
