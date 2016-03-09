<?php
/*======================================================================*\
|| #################################################################### ||
|| # vBGamEz 6.0 Beta 4
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2008-20011 vBGamEz Team. All Rights Reserved.            ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBGAMEZ IS NOT FREE SOFTWARE ------------------ # ||
|| # http://www.vbgamez.com                                           # ||
|| #################################################################### ||
\*======================================================================*/

if (!isset($GLOBALS['vbulletin']->db))
{
	exit;
}

/**
 * VBGamEz Paid Functions
 *
 * @package vBGamEz
 * @author vBGamEz Development Team
 * @version $Revision: 100 $
 * @copyright vBGamEz Development Team
 */

class vBGamez_Paid
{
	// The VB Registry
	private $registry;
	
	// Cached Active subscribtions
	private $subscribed;
	
	// Cached server info
	private $cache;
	
	public static function instance($registry)
	{
		return new vBGamez_Paid($registry);
	}
	
	public function __construct($registry)
	{
		$this->registry =& $registry;
		if(!$registry->options['vbgamez_paid_enable'] OR !$registry->options['vbgamez_paid_ids'])
		{
			return false;
		}
		$registry->options['vbgamez_paid_ids'] = intval($registry->options['vbgamez_paid_ids']);
		
		$susers = $this->registry->db->query_first("
			SELECT *
			FROM " . TABLE_PREFIX . "subscriptionlog
			WHERE status = 1
			AND userid = " . $this->registry->userinfo['userid'] . " AND subscriptionid = " . $registry->options['vbgamez_paid_ids'] . "
		");

	 	if($susers['expirydate'] >= TIMENOW)
		{
			$this->subscribed =& $susers;
		}
	}
	
	public function canStickyServer()
	{
		return !empty($this->subscribed);
	}
	
	public function canPay()
	{
		if(!$this->registry->userinfo['userid'])
		{
			return false;
		}
		
		return true;
	}
	
	public function isServerOwner($serverid)
	{
		$info = vB_vBGamez::vbgamez_verify_id($serverid);
		$this->cache = $info;
		if($info['userid'] == $this->registry->userinfo['userid'])
		{
			return true;
		}
		return false;
	}
	
	public function getServerName()
	{
		return vB_vBGamez::vbgamez_string_html($this->cache['cache_name']);
	}
	
	public function buildPaymentForm()
	{
		global $vbphrase;
		
		$_CURRENCYSYMBOLS = array(
			'usd' => 'US$',
			'gbp' => '&pound;',
			'eur' => '&euro;',
			'cad' => 'CA$',
			'aud' => 'AU$',
		);
		
		$lengths = array(
			'D' => $vbphrase['day'],
			'W' => $vbphrase['week'],
			'M' => $vbphrase['month'],
			'Y' => $vbphrase['year'],
			// plural stuff below
			'Ds' => $vbphrase['days'],
			'Ws' => $vbphrase['weeks'],
			'Ms' => $vbphrase['months'],
			'Ys' => $vbphrase['years']
		);
		
		$subscription = $this->registry->db->query_first("SELECT * FROM " . TABLE_PREFIX . "subscription WHERE subscriptionid = " . intval($this->registry->options['vbgamez_paid_ids']) . "");

		$subscription['cost'] = unserialize($subscription['cost']);
		$string = '<option value="">--------</option>';
		foreach ($subscription['cost'] AS $key => $currentsub)
		{
			if ($currentsub['length'] == 1)
			{
				$currentsub['units'] = $lengths["{$currentsub['units']}"];
			}
			else
			{
				$currentsub['units'] = $lengths[$currentsub['units'] . 's'];
			}
			$string .= "<optgroup label=\"" . construct_phrase($vbphrase['vbgamez_length_x_units_y_recurring_z'], $currentsub['length'], $currentsub['units'], ($currentsub['recurring'] ? ' *' : '')) . "\">\n";
			foreach ($currentsub['cost'] AS $currency => $value)
			{
				if ($value > 0)
				{
					$string .= "<option value=\"{$key}_{$currency}\" >" . $_CURRENCYSYMBOLS["$currency"] . vb_number_format($value, 2) . "</option>\n";
				}
			}
			$string .= "</optgroup>\n";
		}

		return $string;

	}
	
	public function deleteSubscription()
	{
		$this->registry->db->query_write("
			DELETE FROM " . TABLE_PREFIX . "subscriptionlog
			WHERE userid = " . $this->registry->userinfo['userid'] . " AND subscriptionid = " . $this->registry->options['vbgamez_paid_ids'] . "
		");
	}
	
	public function stickyServer($id)
	{
		$expirydate = $this->subscribed['expirydate'];
				
		$this->registry->db->query("UPDATE " . TABLE_PREFIX . "vbgamez SET stick = 1, expirydate = '$expirydate' WHERE id = $id");
		$this->registry->db->query("INSERT INTO " . TABLE_PREFIX . "vbgamez_subscribe 
									(userid, serverid, date, expirydate) VALUES ('" . $this->registry->userinfo['userid'] . "', '$id', '" . TIMENOW . "', '" . $expirydate . "')");
									
	}
	
	public function isAlreadySticked()
	{
		if($this->cache['stick'] AND !$this->registry->options['vbgamez_renew_enable'])
		{
			return true;
		}
		return false;
	}
	public function getServerConnection()
	{
		$ipaddress = $this->cache['ip'].':'.$this->cache['c_port'];
		return $ipaddress;
	}
	
	public static function paidIsEnabled(vB_Registry $registry, $renew = false)
	{
		static $subscription;
		static $apitypes;
		if(!$registry->options['vbgamez_paid_enable'] OR !$registry->options['vbgamez_paid_ids'])
		{
			$GLOBALS['vbgamez_errorid'] = 'vbgamez_disabled_or_empty_paidid';
			return false;
		}
		if(!$subscription)
		{
			$subscription = $registry->db->query_first("SELECT * FROM " . TABLE_PREFIX . "subscription WHERE subscriptionid = " . intval($registry->options['vbgamez_paid_ids']) . "");
		}
		
		if(!$subscription['subscriptionid'])
		{
			$GLOBALS['vbgamez_errorid'] = 'vbgamez_subscription_not_found';
			return false;
		}
		
		if(!$apitypes)
		{
			$apitypes = $registry->db->query_first("SELECT * FROM " . TABLE_PREFIX . "paymentapi WHERE active = 1");
		}
		
		if(empty($apitypes['paymentapiid']))
		{
			$GLOBALS['vbgamez_errorid'] = 'vbgamez_api_not_configured';
			return false;
		}
		
		if($renew AND !$registry->options['vbgamez_renew_enable'])
		{
			return false;
		}
		
		return true;
	}
	
	public function getExpiryDate()
	{
		$date = ($this->subscribed['expirydate'] ? $this->subscribed['expirydate'] : $this->cache['expirydate']);
		if($date)
		{
			return vbdate($this->registry->options['dateformat'].' '.$this->registry->options['timeformat'], $date);
    	}else{
			return false;
		}
	}
}