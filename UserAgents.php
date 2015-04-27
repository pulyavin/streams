<?php namespace pulyavin\streams;

/**
 * Class UserAgents
 * @package pulyavin\streams
 */
class UserAgents
{
    private $save;

    private function get_rLocale()
    {
        return 'en-' . $this->rander(['US', 'AU', 'CA', 'IN', 'IE', 'MT', 'NZ', 'PH', 'SG', 'ZA', 'GB', 'US']);
    }

    private function get_rDotClr()
    {
        return (rand(0, 1)) ? ' .NET CLR ' . rand(2, 3) . '.' . rand(1, 8) . '.' . rand(3, 5) . '07' . rand(0, 9) . rand(0, 9) . ';' : '';
    }

    private function get_rDotClr12()
    {
        return (rand(0, 1)) ? ' .NET CLR ' . rand(1, 2) . '.' . rand(0, 1) . '.' . rand(4, 5) . '07' . rand(0, 5) . rand(0, 9) . ';' : '';
    }

    private function get_rMedSrv()
    {
        return (rand(0, 1)) ? ' Media Center PC ' . rand(4, 6) . '.0;' : '';
    }

    private function get_rOSwin68()
    {
        return 'Windows NT ' . rand(6, 8) . '.' . rand(0, 1);
    }

    private function get_rOSwin56()
    {
        return 'Windows NT ' . rand(5, 6) . '.' . rand(0, 1);
    }

    private function get_rOSmacGnd()
    {
        return 'Intel Mac OS X 10_' . rand(6, 8) . '_' . rand(2, 8);
    }

    private function get_rOSmacDot()
    {
        return 'Intel Mac OS X 10.' . rand(5, 8);
    }

    private function get_rOSlinux()
    {
        return $this->rander(['NetBSD amd64', 'Linux amd64', 'Linux x86_64', 'Ubuntu; Linux', 'SunOS sun4u']);
    }

    private function get_rChrome3x()
    {
        return 'Chrome/3' . rand(1, 7) . '.0.20' . rand(0, 4) . rand(0, 9) . '.' . rand(1, 9) . rand(0, 9);
    }

    private function get_rOpera()
    {
        return 'Opera/' . rand(9, 12) . '.' . $this->rander(['50', '60', '0', '80']);
    }

    private function rander($array)
    {
        return $array[array_rand($array)];
    }

    private function save($save = "")
    {
        if ($save) {
            $this->save = $save;
        }

        return $this->save;
    }

    public function getRand($slice = null)
    {
        unset($this->save);

        if ($slice && !is_array($slice)) {
            $slice = [$slice];
        }

        $browsers = [
            "IE10"      => "Mozilla/5.0 (compatible; MSIE 10.0; " . $this->get_rOSwin68() . ";" . $this->rander([" InfoPath." . rand(2, 3) . ";", ""]) . " " . $this->get_rDotClr() . " " . $this->rander([" InfoPath." . rand(2, 3) . ";", ""]) . " " . $this->rander(["WOW64; ", ""]) . " Trident/" . rand(5, 6) . ".0" . $this->rander(["; " . $this->get_rLocale(), ""]) . ")",
            "IE9"       => "Mozilla/5.0 (" . $this->rander(["compatible", "Windows; U"]) . "; MSIE 9.0; " . $this->get_rOSwin68() . "; " . $this->rander(["Win64; x64; ", "WOW64; ", ""]) . " Trident/" . rand(4, 5) . ".0;" . $this->get_rDotClr() . "" . $this->get_rMedSrv() . "" . $this->rander([" Zune 4." . rand(0, 7) . ";", "", "", ""]) . " .NET4.0" . $this->rander(["C", "E"]) . "; " . $this->get_rLocale() . ")",
            "IE8"       => "Mozilla/" . $this->rander([5, 5, 4]) . ".0 (compatible; MSIE 8.0; " . $this->get_rOSwin56() . "; Trident/4.0; " . $this->rander(["WOW64", "WOW64", "GTB7." . rand(2, 6)]) . "; InfoPath." . rand(2, 3) . ";" . $this->rander([" SV1;", ""]) . "" . $this->get_rDotClr() . " " . $this->get_rLocale() . ")",
            "IE7"       => "Mozilla/" . $this->rander([5, 4, 4]) . ".0 (" . $this->rander(["compatible", "compatible", "Windows; U"]) . "; MSIE 7.0; " . $this->get_rOSwin56() . "; " . $this->rander([" WOW64;", ""]) . "" . $this->get_rDotClr() . $this->get_rMedSrv() . " InfoPath." . rand(1, 3) . "; " . $this->get_rLocale() . ")",
            "IE6"       => "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5." . rand(0, 1) . "; " . $this->rander([" SV1;", "", ""]) . "" . $this->get_rDotClr12() . " " . $this->get_rLocale() . ")",
            "ChromeWin" => "Mozilla/5.0 (" . $this->get_rOSwin68() . "" . $this->rander(["; Win64; x64", "; WOW64", ""]) . ") AppleWebKit/" . $this->save("53" . rand(6, 7) . "." . rand(1, 3) . "" . rand(1, 7)) . " (KHTML, like Gecko) " . $this->get_rChrome3x() . " Safari/" . $this->save(),
            "ChromeMac" => "Mozilla/5.0 (Macintosh; " . $this->get_rOSmacGnd() . ") AppleWebKit/" . $this->save("53" . rand(6, 7) . "." . rand(1, 3) . "" . rand(1, 7)) . " (KHTML, like Gecko) " . $this->get_rChrome3x() . " Safari/" . $this->save(),
            "ChromeLin" => "Mozilla/5.0 (X11;" . $this->rander([" U; ", " "]) . "Linux " . $this->rander(["x86_64", "i686"]) . ") AppleWebKit/" . $this->save("53" . rand(6, 7) . "." . rand(1, 3) . "" . rand(1, 7)) . " (KHTML, like Gecko) " . $this->get_rChrome3x() . " Safari/" . $this->save(),
            "FFWin"     => "Mozilla/5.0 (" . $this->get_rOSwin68() . "; " . $this->rander(["WOW64", "Win64"]) . "; rv:" . $this->save("2" . rand(0, 5) . ".0") . ") Gecko/20100101 Firefox/" . $this->save(),
            "FFMac"     => "Mozilla/5.0 (Macintosh;" . $this->rander([" U; ", " "]) . "" . $this->get_rOSmacDot() . "; rv:" . $this->save("2" . rand(0, 5) . ".0") . ") Gecko/20100101 Firefox/" . $this->save(),
            "FFLin"     => "Mozilla/5.0 (X11; " . $this->get_rOSlinux() . "; rv:" . $this->save("2" . rand(0, 5) . ".0") . ") Gecko/20100101 Firefox/" . $this->save(),
            "SafariWin" => "Mozilla/5.0 (Windows; U; " . $this->get_rOSwin68() . "; " . $this->get_rLocale() . ") AppleWebKit/" . $this->save("5" . rand(2, 3) . rand(2, 8) . "." . rand(1, 2) . rand(0, 9) . "." . rand(1, 2) . rand(0, 9)) . " (KHTML, like Gecko) Version/5.0." . rand(2, 4) . " Safari/" . $this->save(),
            "SafariMac" => "Mozilla/5.0 (Macintosh;" . $this->rander([" U; ", " "]) . "" . $this->get_rOSmacGnd() . "; " . $this->get_rLocale() . ") AppleWebKit/" . $this->save("5" . rand(2, 3) . rand(2, 8) . "." . rand(1, 2) . rand(0, 9) . "." . rand(1, 3) . rand(0, 9)) . " (KHTML, like Gecko)" . $this->rander([" Version/5.0." . rand(2, 4) . " ", " "]) . "Safari/" . $this->save(),
            "SafariLin" => "Mozilla/5.0 (X11; " . $this->get_rOSlinux() . "; " . $this->get_rLocale() . ") AppleWebKit/" . $this->save("5" . rand(2, 3) . rand(2, 8) . "." . rand(1, 2) . rand(0, 9) . "." . rand(1, 3) . rand(0, 9)) . " (KHTML, like Gecko)" . $this->rander([" Version/5.0." . rand(2, 4) . " ", " "]) . "Safari/" . $this->save(),
            "OperaWin"  => $this->get_rOpera() . " " . $this->rander(["(compatible; MSIE 9.0; ", "("]) . "" . $this->get_rOSwin68() . "; " . $this->get_rLocale() . ") Presto/2." . $this->rander(["9", "11", "12"]) . "." . $this->rander([rand(1, 3), ""]) . rand(0, 9) . rand(0, 9) . " Version/1" . rand(1, 2) . "." . $this->rander(["1", rand(5, 6)]) . "" . rand(0, 2),
            "OperaMac"  => $this->get_rOpera() . " (Macintosh; " . $this->get_rOSmacDot() . ";" . $this->rander([" U; ", " "]) . "" . $this->get_rLocale() . ") Presto/2." . $this->rander(["9", "11", "12"]) . "." . $this->rander([rand(1, 3), ""]) . rand(0, 9) . rand(0, 9) . " Version/1" . rand(1, 2) . "." . $this->rander(["1", rand(5, 6)]) . "" . rand(0, 2),
            "OperaLin"  => $this->get_rOpera() . " (X11; Linux " . $this->rander(["i686", "x86_64"]) . "; U; " . $this->get_rLocale() . ") Presto/2." . $this->rander(["9", "11", "12"]) . "." . $this->rander([rand(1, 3), ""]) . rand(0, 9) . rand(0, 9) . " Version/1" . rand(1, 2) . "." . $this->rander(["1", rand(5, 6)]) . "" . rand(0, 2),
            "iPhone"    => "Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_" . rand(2, 3) . "_" . rand(1, 3) . " like Mac OS X; " . $this->get_rLocale() . ") AppleWebKit/53" . rand(3, 5) . "." . rand(1, 3) . "" . rand(1, 7) . "." . rand(9, 11) . " (KHTML, like Gecko) Version/5.0.2 Mobile/8" . $this->rander(["J", "F", "C"]) . "" . rand(1, 4) . "" . $this->rander(["8a", "90", ""]) . " Safari/6533.18.5",
            "iPad"      => "Mozilla/5.0 (iPad;" . $this->rander([" U;", ""]) . " CPU OS " . rand(3, 6) . "_" . rand(0, 2) . "" . $this->rander(["_2", ""]) . " like Mac OS X" . $this->rander(["; " . $this->get_rLocale(), ""]) . ") AppleWebKit/53" . rand(1, 6) . "." . rand(1, 2) . "" . rand(1, 7) . "." . rand(9, 11) . " (KHTML, like Gecko) Version/" . rand(4, 6) . "." . rand(0, 1) . "" . $this->rander([".4", ""]) . " Mobile/8" . $this->rander(["J", "F", "C"]) . "" . rand(1, 4) . "" . $this->rander(["8a", "90", ""]) . " Safari/" . $this->rander([rand(7, 8), ""]) . "53" . rand(1, 6) . ".2" . rand(0, 1) . "" . $this->rander([".10", ""]) . "",
        ];

        if (empty($slice)) {
            return $browsers[array_rand($browsers)];
        }

        $browser = $slice[array_rand($slice)];
        if (isset($browsers[$browser])) {
            return $browsers[$browser];
        }

        return;
    }
}
