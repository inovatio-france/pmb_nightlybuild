<template>
    <div class="row ie-row">
        <fieldset class="ie-fieldset">
            <div class="row ie-row" v-for='(setting, index) in visibleSettings' :key="index">
                <div class="colonne25">
                    <label class="etiquette" :for="setting.name">{{ msg[setting.name] }}</label>
                </div>
                <div class="colonne75">
                    <select v-if='setting.type == "select"' :id="setting.name" v-model="formValues[setting.name]" :required="setting.required">
                        <option value="" disabled>{{ messages.get('common', 'common_default_select') }}</option>
                        <option v-for="(option, index) in options[setting.name]" :value="option.value" :key="index">{{ option.label }}</option>
                    </select>
                    <select v-else-if='setting.type == "source"' :id="setting.name" v-model="formValues[setting.name]" :required="setting.required">
                        <option value='' disabled>{{ messages.get('importexport', 'ie_source_empty_value') }}</option>
                        <option v-for='(source, index) in sources' :value='source.id' :key="index">{{ source.sourceName }}</option>
                    </select>
                    <span v-else-if='setting.type == "text" && setting.multiple'>
                        <div class="row ie-row" v-for="(input, index) in formValues[setting.name]" :key="index">
                            <input :type="setting.type" v-model="formValues[setting.name][index].value" :required="setting.required" />
                            <button type="button" class="bouton" @click="deleteMultipleInput(setting.name, index)" :title="messages.get('common', 'remove')">
                                <i class="fa fa-times" aria-hidden="true"></i>
                            </button>
                            <button v-if="index == formValues[setting.name].length - 1" @click="addMultipleInput(setting.name, index, $event)"
                                type="button" class="bouton" :title="messages.get('common', 'more_label')">
                                <i class="fa fa-plus" aria-hidden="true"></i>
                            </button>
                        </div>
                    </span>
                    <input v-else :id="setting.name" :type="setting.type" v-model="formValues[setting.name]" :required="setting.required" />
                </div>
            </div>
            <slot></slot>
        </fieldset>
    </div>
</template>

<script>
export default {
    props : {
        settings : {
            'type' : Array
        },
        msg : {
            'type' : Object
        },
        formValues : {
            'type' : Object
        },
        sources : {
        	'type' : Array,
        	'default' : function() {
        		return [];
            }
        }
    },
    data : function() {
    	return {
    		options : {}
    	}
    },
    created : function() {
    	this.settings.forEach(async (setting) => {
            if(setting.type == 'text' && setting.multiple) {
            	if(this.formValues[setting.name].length == 0) {
            		let values = new Array();
            		values.push({value:''});
            		this.$set(this.formValues, setting.name, values);
            	}
			}
            switch(setting.type) {
                case 'select':
                    if(setting.callback !== undefined) {
                        let response = await this.ws.get(setting.controller, 'callback/' + setting.callback);
                        if(!response.error) {
                            this.$set(this.options, setting.name, response);
                        }
                    }
                    break;
                case "checkbox":
                    if(this.formValues[setting.name] === "0") {
                        this.$set(this.formValues, setting.name, false);
                    } else if(this.formValues[setting.name] === "1") {
                        this.$set(this.formValues, setting.name, true);
                    }
                    break;
            }
    	})
    },
    methods: {
        changeMultipleInput: function (index, event) {

        },
        deleteMultipleInput: function (name, index) {
            if (this.formValues[name].length > 1) {
            	this.formValues[name].splice(index, 1);
            } else {
                this.formValues[name][index].value = '';
            }
        },
        addMultipleInput: function (name, index, event) {
            this.formValues[name].push({value : ''});
        }
    },
    computed : {
        visibleSettings : function() {
            return this.settings.filter(setting => (!setting.custom)||(setting.custom != 'true'));
        }
    }
}
</script>