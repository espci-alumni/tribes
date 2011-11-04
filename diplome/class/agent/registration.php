<?php

class agent_registration extends self
{
    protected function composeForm($o, $f, $send)
    {
        $o = parent::composeForm($o, $f, $send);
        $o = $this->composeDiplome($o, $f, $send);

        return $o;
    }

    protected function composeDiplome($o, $f, $send)
    {
        $promotions = array();

        $sql = "(SELECT COALESCE((SELECT value FROM item_lists WHERE type='promotion/limits' LIMIT 1),'') AS promotion)
            UNION ALL (SELECT value FROM item_lists WHERE type='promotion/label' ORDER BY sort_key)";

        $result = DB()->query($sql);

        if ($row = $result->fetchRow())
        {
            while ($sql = $result->fetchRow())
            {
                $promotions[$sql->promotion] = $sql->promotion;
            }

            $row = explode(':', $row->promotion, 2);

            if (2 === count($row))
            {
                foreach (range($row[0], $row[1]) as $row)
                {
                    $promotions[$row] = $row;
                }
            }
        }

        if ($promotions)
        {
            $f->add('select', 'promotion', array(
                'firstItem' => '- Choisir dans la liste -',
                'item' => $promotions,
            ));
        }
        else
        {
            $f->add('text', 'promotion');
        }

        $send->attach('promotion', 'Veuillez renseignez votre promotion', '');

        return $o;
    }
}
