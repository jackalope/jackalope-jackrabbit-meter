<?php

include("config.inc.php");

$jj = new JJMeter($options);

$jj->run();

class JJMeter
{

    function __construct($options)
    {
        $this->options = $options;
    }

    function run()
    {
        print "****\n";
        foreach ($this->options["pages"] as $url) {
            $this->benchPage($url);
            print "****\n";
        }
    }

    function benchPage($url)
    {

        $ch = $this->getCurl($url);
        $linecount = $this->getLineCountLog();
        $body = curl_exec($ch);
        // test for redirects
        $effUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        if ($effUrl == $this->options['loginGet']) {
            curl_close($ch);
            $this->login();
            return $this->benchPage($url);
        }

        if ($effUrl != $url) {
            //do it again to have a clean plate
            curl_close($ch);
            return $this->benchPage($effUrl);
        }
        $info = curl_getinfo($ch);
        print "TEST for $effUrl\n";
        print "HTTP Code         : " . $info['http_code'] . "\n";
        print "Body size         : " . strlen($body) . " bytes\n";
        print "Request time      : " . round($info["total_time"] * 1000) . " ms\n";
        print "Total JR Requests : " . ($this->getLineCountLog() - $linecount) . "\n";
        $types = array();
        foreach ($this->getLastLinesFromLog($linecount) as $line) {
            $fields = explode(" ", $line);
            $method = trim($fields[7], '"');
            if (!isset($types[$method])) {
                $types[$method] = 1;
            } else {
                $types[$method]++;
            }
        }
        ksort($types);
        foreach ($types as $name => $count) {
            print " " . $name . str_repeat(" ", 17 - strlen($name)) . ": " . $count . "\n";
        }

        return $body;
    }

    function login()
    {
        $ch = $this->getCurl($this->options['loginGet']);
        curl_exec($ch);

        $ch = $this->getCurl($this->options['loginPost']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->options['loginParams']));
        curl_exec($ch);
        curl_close($ch);
    }

    function getLogHandle()
    {
        return fopen($this->options['jackrabbitDir'] . "/log/access.log." . date_format(new DateTime(), "Y-m-d"), "r");
    }

    function getLineCountLog()
    {

        $log = $this->getLogHandle();
        $count = 0;
        while (fgets($log)) {
            $count++;
        }
        fclose($log);
        return $count;
    }

    function getLastLinesFromLog($from)
    {
        $log = $this->getLogHandle();
        $count = 0;
        while (fgets($log) && $count < $from - 1) {
            $count++;
        }
        $lines = array();
        while ($l = fgets($log)) {
            $lines[] = $l;
        }
        return $lines;
    }

    function getCurl($url)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIEJAR, "/tmp/cookieFileName");
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/cookieFileName");
        return $ch;
    }

}