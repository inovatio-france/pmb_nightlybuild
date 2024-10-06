<?php
// +-------------------------------------------------+
// © 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_artecampus_view_artecampus.class.php,v 1.4 2024/08/28 16:00:28 gneveu Exp $

use Pmb\Common\Orm\EmprOrm;

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) {
    die("no access");
}

class cms_module_artecampus_view_artecampus extends cms_module_common_view_django
{
    public function __construct($id = 0)
    {
        parent::__construct($id);
        $this->default_template = '
<div class="artecampus">
    {% if !session_vars.id_empr %}
        <img
            src="./images/connecteurs/artecampus.svg"
            alt="{{ msg.artecampus }}"
        />
        <input
            class="bouton" type="button"
            onclick="auth_popup(\'./ajax.php?module=ajax&categ=auth&callback_func=artecampus_callback_auth\')"
            value="{{ msg.artecampus_empr_login }}"
        />
        <script>
            if (typeof artecampus_callback_auth === "undefined") {
                function artecampus_callback_auth(id_empr) {
                    window.location.reload();
                }
            }
        </script>
    {% else %}
        <img
            src="./images/connecteurs/artecampus.svg"
            alt="{{ msg.artecampus }}"
        />
        <input
            class="bouton" type="submit"
            form="{{ id }}_artecampus_form"
            value="{{ msg.artecampus_see }}"
        />
        <form
            id="{{ id }}_artecampus_form"
            action="{{ connector.url }}"
            target="_blank"
            method="post"
        >
            <input type="hidden" name="data" value="{{ connector.empr_data }}" />
        </form>
    {% endif %}
</div>';
    }

    /**
     * Renvoie les structures de données du module
     *
     * @return array
     */
    public function get_format_data_structure()
    {
        return array_merge(
            parent::get_format_data_structure(),
            [
                [
                    'var' => 'connector',
                    'desc' => $this->msg['connector_vars_view_desc'],
                    'children' => [
                        [
                            'var' => 'connector.url',
                            'desc' => $this->msg['connector_url_vars_view_desc'],
                        ],
                        [
                            'var' => 'connector.empr_data',
                            'desc' => $this->msg['connector_empr_data_vars_view_desc'],
                        ]
                    ]
                ]
            ]
        );
    }

    /**
     * Rendu du module
     *
     * @param false|array{connector: int} $data
     * @return string
     */
	public function render($data)
    {
        if (false === $data || empty($data['connector'])) {
            return '';
        }

        try {
            if (!empty($_SESSION['id_empr_session'])) {
                $empr = new EmprOrm($_SESSION['id_empr_session']);
                $emails = explode(';', $empr->empr_mail);
                $email = $emails[0] ?? '';

                if (empty($email)) {
                    throw new Exception('Email not found');
                }

                $connector = new artecampus();
                $hmac = $connector->generate_hmac($email, $data['connector']);
                $payload = $connector->generate_data($empr, $data['connector']);
            } else {
                $hmac = '';
                $payload = [];
            }
        } catch (Exception $e) {
		    $html = '<!-- '.$e->getMessage().' -->';
		    $html .= '<div class="error_on_template" title="' . htmlspecialchars($e->getMessage(), ENT_QUOTES) . '">';
		    $html .= $this->msg["cms_module_common_view_error_template"];
		    $html .= '</div>';
            return $html;
        }

        global $charset;
	    return parent::render([
            'connector' => [
                'url' => artecampus::LOGIN_URL . '?' . http_build_query(['hmac' => $hmac]),
                'empr_data' => htmlentities(encoding_normalize::json_encode($payload), ENT_QUOTES, $charset)
            ]
        ]);
    }
}
