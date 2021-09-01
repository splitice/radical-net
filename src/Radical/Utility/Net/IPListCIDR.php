<?php
namespace Radical\Utility\Net;

use Radical\Utility\Net\CIDR;
use Radical\Utility\Net\CIDRRange;

/**
 * Class for manipulating IP lists and CIDR lists
 *
 */
class IPListCIDR
{
	/**
	 * Convert a list of IPv4 addresses (or numbers) into a list of ranges
	 *
	 * @param int[] $ips array of sorted IPv4 addresses as integers
	 * @return int[]
	 */
	function ip_to_range($ips, $sorted = false)
	{
	    if(!$sorted) {
            $ips = array_values($ips);
            sort($ips);
        }

        if((is_array($ips) || $ips instanceof \Countable) && count($ips) <= 2) {
            return $ips;
        }


	    $s = null;
		$ret = array();
        if(is_array($ips)){
            $ips = new \ArrayIterator($ips);
        }
		while($current = $ips->current()) {
			$ips->next();
            $next = $ips->current();

			if ($next !== false && $current + 1 == $next) {
                if($s === null) $s = $current;
            }else{
			    if($s)
			    {
			        $ret[] = array($s, $current);
			        $s = null;
                }
                else
                {
                    $ret[] = $current;
                }
            }
		}

		return $ret;
	}

	/**
	 * Convert a list of IPv4 addresses into a list of CIDRs
	 *
	 * @param int[] $ips IPv4 addresses in long form
	 * @return string[] an array of CIDR's and IP addresses that would contain all the supplied IPs
	 */
	public function to_cidr_list($ips, $short_syntax = true, $sorted = false)
	{
		$ip_ranges = $this->ip_to_range($ips, $sorted);

		$cidrs = array();
		foreach ($ip_ranges as $range) {
			if (!is_array($range)) {
				$ip = \IP::create($range);
				$cidrs[] = (string)$ip;
			} else {
				$range[0] = \IP::create($range[0]);
				$range[1] = \IP::create($range[1]);
				foreach (CIDRRange::rangeToCIDRList((string)$range[0],(string)$range[1], $short_syntax) as $c) {
					$cidrs[] = $c;
				}
			}
		}

		return $cidrs;
	}

	/**
	 * Convert a list of CIDRs into a list of IPv4 addresses (number format)
	 *
	 * @param $ips
	 */
	public function cidr2long(&$ips)
	{
		foreach ($ips as $k => $v) {
			if (strpos($v, '/') || $v instanceof \IPBlock) {
				try {
					$v = ($v instanceof \IPBlock) ? $v : \IPBlock::create($v);
				}catch (\Exception $ex){
					continue;
				}
				foreach ($v as $kk => $i) {
					if ($kk == 0) {
						$ips[$k] = $i->numeric();
					} else {
						$ips[] = $i->numeric();
					}
				}
			} else {
				try {
					$ips[$k] = \IP::create($v)->numeric();
				}catch (\Exception $ex){
					continue;
				}
			}
		}
	}

    /**
     * Convert a list of CIDRs into a list of IPv4 addresses (number format)
     *
     * @param $ips
     */
    public function cidr2longGenerator($ips, $sorted = false)
    {
        foreach ($ips as $k => $v) {
            if (strpos($v, '/') || $v instanceof \IPBlock) {
                try {
                    $v = ($v instanceof \IPBlock) ? $v : \IPBlock::create($v);
                }catch (\Exception $ex){
                    continue;
                }
                if($sorted) {
                    foreach ($v as $kk => $i) {
                        if ($kk == 0) {
                            yield $i->numeric();
                        } else {
                            yield $i->numeric();
                        }
                    }
                }else{
                    $ips[$k] = $v;
                }
            } else {
                try {
                    $v = \IP::create($v);
                }catch (\Exception $ex){
                    continue;
                }
                if(!$sorted){
                    $ips[$k] = $v;
                }else{
                    yield $v->numeric();
                }
            }
        }
        if(!$sorted){
            $numeric = function($v){
                if($v instanceof \IPBlock){
                    return $v->getNetworkAddress()->numeric();
                }
                return $v->numeric();
            };
            usort($ips, function($a, $b) use($numeric){
                $a = $numeric($a);
                $b = $numeric($b);

                if($a < $b) return -1;
                if($b > $a) return 1;
                return 0;
            });

            foreach($ips as $v){
                if($v instanceof \IP){
                    yield $v->numeric();
                }else{
                    foreach ($v as $kk => $i) {
                        if ($kk == 0) {
                            yield $i->numeric();
                        } else {
                            yield $i->numeric();
                        }
                    }
                }
            }
        }
    }

	/**
	 * Lossy conversion to subnets of a specific $cidr as long as $number_req is met
	 *
	 * @param $ips
	 * @param $cidr
	 * @param $number_req
	 * @return string[] CIDR's containing the IPs removed from $ips
	 */
	public function subnet_reduce(&$ips, $cidr, $number_req)
	{
		$cidr24s = array();
		$ipmask = -1 << (32 - (int)$cidr);

		foreach ($ips as $k => $v) {
			$mask = $v & $ipmask;
			if (!isset($cidr24s[$mask])) $cidr24s[$mask] = 0;
			$m = ++$cidr24s[$mask];
			if ($m >= $number_req) unset($ips[$k]);
		}

		foreach($cidr24s as $k=>$v){
            if ($cidr24s[$k] < $number_req) unset($cidr24s[$k]);
        }

		foreach ($ips as $k => $v) {
			if (isset($cidr24s[$v & $ipmask])) unset($ips[$k]);
		}

		$append = '/' . $cidr;
		foreach ($cidr24s as $mk => $mv) {
            $cidr24s[$mk] = long2ip($mk) . $append;
		}

		return array_values($cidr24s);
	}
}