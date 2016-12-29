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
	function ip_to_range($ips)
	{
		$ips = array_values($ips);
		sort($ips);
		$lastindex = count($ips) - 1;
		$ret = array();
		$s = null;
		foreach ($ips as $i => $n) {
			if ($i == 0)
				$s = $n;
			else if ($ips [$i - 1] + 1 != $n) {
				if ($s != null)
					$ret[] = $s;

				$s = $n;
			} else if ($i == $lastindex || $n + 1 != $ips [$i + 1]) {
				$ret[] = array($s, $n);
				$s = null;
			}
		}
		if ($s !== null) {
			$ret[] = $s;
		}

		return $ret;
	}

	/**
	 * Convert a list of IPv4 addresses into a list of CIDRs
	 *
	 * @param int[] $ips IPv4 addresses in long form
	 * @return string[] an array of CIDR's and IP addresses that would contain all the supplied IPs
	 */
	public function to_cidr_list($ips)
	{
		$ip_ranges = $this->ip_to_range($ips);
		$cidrs = array();
		foreach ($ip_ranges as $range) {
			if (!is_array($range)) {
				$ip = \IP::create($range);
				$cidrs[] = (string)$ip;
			} else {
				$range[0] = \IP::create($range[0]);
				$range[1] = \IP::create($range[1]);
				foreach (CIDRRange::rangeToCIDRList((string)$range[0],(string)$range[1]) as $c) {
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
				$v = ($v instanceof \IPBlock) ? $v : \IPBlock::create($v);
				foreach ($v as $kk => $i) {
					if ($kk == 0) {
						$ips[$k] = $i->numeric();
					} else {
						$ips[] = $i->numeric();
					}
				}
			} else {
				$ips[$k] = \IP::create($v)->numeric();
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
		$cidrs = array();
		$ipmask = -1 << (32 - (int)$cidr);

		foreach ($ips as $k => $v) {
			$mask = $v & $ipmask;
			if (!isset($cidr24s[$mask])) {
				$cidr24s[$mask] = 0;
			}
			$m = ++$cidr24s[$mask];
			if ($m >= $number_req) {
				unset($ips[$k]);
			}
		}

		foreach ($ips as $k => $v) {
			$mask = $v & $ipmask;
			if ($cidr24s[$mask] >= $number_req) {
				unset($ips[$k]);
			}
		}

		foreach ($cidr24s as $mk => $mv) {
			if ($mv >= $number_req) {
				$cidrs[] = long2ip($mk) . '/' . $cidr;
			}
		}

		return $cidrs;
	}
}