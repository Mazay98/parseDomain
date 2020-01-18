<?php
class Date
{
    public function getRegistryDate($date)
    {
        preg_match('/Creation\sDate:\s(.*)\\r/', $date, $matches);
        if (!$matches[1]){
            preg_match('/created:\s*(.*)\\n/', $date, $matches);
        }
        $matches[1] = $this->formatDate($matches[1]);
        return $matches[1];
    }

    public function getExpirationDate($date)
    {
        preg_match('/Registry\sExpiry\sDate:\s(.*)\\r/', $date, $matches);
        if (!$matches[1]){
            preg_match('/paid-till:\s*(.*)\\n/', $date, $matches);
        }
        $matches[1] = $this->formatDate($matches[1]);
        return $matches[1];
    }

    public function addExpAndRegDate($url, $ans, $db)
    {
        // Проверка на длину строки ответа
        if (strlen($ans)<=50) {
            return 'Ошибка в домене';
        }

        $domain_id = self::getDomainId($url, $db);

        if (!$domain_id) {
            $reg = $this->getRegistryDate($ans);
            $exp = $this->getExpirationDate($ans);

            $insert = $db->prepare('INSERT INTO domains (domain_name, date_start, date_end) VALUES (:domain_name, :date_start, :date_end)');
            $insert->execute([':domain_name' => $url, ':date_start' => $reg, ':date_end' => $exp]);
            return $this->addExpAndRegDate($url,$db);
        }
        return (int)$domain_id[0]['domain_id'];
    }
    public static function getDomainId($domain, $db)
    {
        $sql= "SELECT domain_id FROM domains WHERE domain_name=?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$domain]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }
    private function formatDate($date)
    {
        preg_match("/(\d{4}-\d{2}-\d{2})/", $date, $matches);
        return $matches[1];
    }

}