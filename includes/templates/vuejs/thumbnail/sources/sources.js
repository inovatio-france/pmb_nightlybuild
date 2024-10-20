import Vue from "vue";

import Messages from "../../common/helper/Messages.js";
Vue.prototype.messages = Messages;

import Images from "../../common/helper/Images.js";
Vue.prototype.images = Images;

import Notif from "../../common/helper/Notif.js";
Vue.prototype.notif = Notif;

import Helper from "../../common/helper/Helper.js";
Vue.prototype.helper = Helper;

import WebService from "../../common/helper/WebService.js";
Vue.prototype.ws = new WebService($data.url_webservice);

import sources from "./components/sources.vue";

new Vue({
	el : "#sources",
	data : {...$data},
	components : {
		sources
	}
});