var mfa_counter = 30;
var dflt_value = "";

function mfa_counter_down() {
    let mfa_mail_btn = document.getElementById('btn_send_mail');
    if(mfa_counter) {
        mfa_mail_btn.textContent = dflt_value + '(' + mfa_counter + ')'
        mfa_counter = mfa_counter - 1;

        setTimeout(mfa_counter_down, 1000);
    } else {
        mfa_counter = 30;
        mfa_mail_btn.textContent = dflt_value;
        mfa_mail_btn.disabled = false;
    }
}

function send_ajax_login(event) {
    event.preventDefault();

    let user = document.getElementById('user').value;
    let password = document.getElementById('password').value;

    let otp_element = document.getElementById('otp');
    let otp = otp_element.value;

    let form = document.getElementById('login');
    let mfa_form = document.getElementById('mfa-login');

    let error = document.getElementById('error');
    let error_mfa = document.getElementById('error-mfa');

    let btn_send_mail = document.getElementById('btn_send_mail');

    let req = new http_request();

    let params = 'user=' + encodeURIComponent(user) + '&password=' + encodeURIComponent(password);
    if(otp.length) {
        params += '&otp=' + encodeURIComponent(otp);
    }

    req.request('./main.php', 1, params);

    if(isJSON(req.get_text())) {
        if (error.children[0] != undefined) {
            error.removeChild(error.children[0]);
        }

        let response = JSON.parse(req.get_text());
        if(response.user) {
            mfa_form.style = "";
            if(btn_send_mail) {
                btn_send_mail.dataset.user = response.user;
            }
            otp_element.focus();

            if(response.favorite == "mail" && response.user_email) {
                send_code_otp("send_mail");
            }
            if(!response.user_email) {
                btn_send_mail.style.display = "none";
            }
            
            return;
        }
        
        if(response.error_otp) {
            error_mfa.children[0].style = "";
            return;
        }
    }
    
    form.submit();
}

function send_code_otp(action = 'send_mail') {
    let req = new http_request();

    switch(action) {
        case 'send_mail':
            let btn_send_mail = document.getElementById('btn_send_mail');
            let notify = document.getElementById('mfa-notify');

            let user = document.getElementById('user').value;
            let password = document.getElementById('password').value;

            if(!btn_send_mail.disabled) {
                req.request('./main.php?action=mail_otp_code', 1, 'user=' + user + '&password=' + password);
                
                if(isJSON(req.get_text())) {
                    let response = JSON.parse(req.get_text());
                    if(response.message) {
                        notify.textContent = response.message;
                        
                        dflt_value = btn_send_mail.textContent;
                        
                        btn_send_mail.disabled = true;
                        mfa_counter_down();
                    }
                }
            }
            break;
        case 'send_sms':
        	// TODO
            break;
        default:
            break;
    }
}

function isJSON(text){
    if (typeof text !== 'string') {
        return false;
    }
    try {
        let json = JSON.parse(text);
        return (typeof json === 'object');
    } catch (error) {
        return false;
    }
}