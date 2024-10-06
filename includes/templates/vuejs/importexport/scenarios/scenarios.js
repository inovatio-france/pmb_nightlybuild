import Vue from "vue";
import edit from "./components/edit.vue";
import loader from "@/common/loader/loader.vue";
import list from "./components/list.vue";
import execution from "./components/execution.vue";

import InitVue from "@/common/helper/InitVue.js";

InitVue(Vue, {
	urlWebservice: $data.url_webservice,
	useLoader: true,
    webserviceCachingOptions: {
        lifetime: 1000
    }
});

new Vue({
	el : "#scenarios",
	data : {
		...$data
	},
	components : {
		list,
		edit,
		execution,
		loader
	}
});