<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SubscriberDiffusion.php,v 1.1 2023/02/07 10:51:15 rtigero Exp $

namespace Pmb\DSI\Models\SubscriberList\Subscribers;

interface SubscriberDiffusion {
	public function getIdSubscriber();
	public function getName();
	public function getStatus();
}