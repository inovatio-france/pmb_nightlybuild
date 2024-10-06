<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: analytics_service_eulerian.class.php,v 1.1 2024/02/21 13:42:05 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class analytics_service_eulerian {
	
	public static function get_label() {
		return "Eulerian";
	}
	
	public static function get_parameters_content_form($parameters=array()) {
		
		return '
		<div class="row">
			<label class="etiquette" for="analytics_service_parameters_domain">domain</label>
		</div>
		<div class="row">
			<input type="text" class="saisie-40em" id="analytics_service_parameters_domain" name="analytics_service_parameters[domain]" value="'.(!empty($parameters['domain']) ? $parameters['domain'] : '').'" />
		</div>
        <div class="row">
			<label class="etiquette" for="analytics_service_parameters_entity">entity</label>
		</div>
		<div class="row">
			<input type="text" class="saisie-40em" id="analytics_service_parameters_entity" name="analytics_service_parameters[entity]" value="'.(!empty($parameters['entity']) ? $parameters['entity'] : '').'" />
		</div>';

	}
	
	public static function get_default_template() {
		return "
		<script>
			window.dsfr = {
                analytics: {
                    domain: '{{ domain }}',
                    // collection: 'manual',
                    // isActionEnabled: true,
                    page: {
                        template: 'article'
                    },
                    user: {
                        // ...
                    },
                    site: {
                        entity: '{{ entity }}', // Entity responsible for website
                    }
                }
            };
		</script>
		";
	}
	
	public static function get_default_consent_template() {
		return "
		<script>
	        tarteaucitron.services.eulerian = {
    	        'key': 'eulerian',
    	        'type': 'analytic',
    	        'name': 'Eulerian Analytics',
    	        'needConsent': true,
    	        'cookies': ['etuix'],
    	        'uri' : '{{ domain }}',
    	        'js': function () {
    	        'use strict';
    	        (function(x,w){ if (!x._ld){ x._ld = 1;
    	        let ff = function() { if(x._f){x._f('tac',tarteaucitron,1)} };
    	        w.__eaGenericCmpApi = function(f) { x._f = f; ff(); };
    	        w.addEventListener('tac.close_alert', ff);
    	        w.addEventListener('tac.close_panel', ff);
    	        }})(this,window);
    	        },
    	        'fallback': function () { this.js(); }
    	    };
            (tarteaucitron.job = tarteaucitron.job || []).push('eulerian');
        </script>";
	}
}