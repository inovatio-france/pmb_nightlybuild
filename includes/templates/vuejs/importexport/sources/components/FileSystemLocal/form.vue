<template>
    <div id="local-file-form" v-if="execution">
        <settings-form :msg="currentType.msg" :form-values="formValues" :settings="currentType.contextParameters">
            <template>
            	<div class="row ie-row">
            		<div class="colonne25">
                        <label class="etiquette" for="encoding">{{ currentType.msg.encoding }}</label>
                    </div>
                    <div class="colonne75">
                    	<select id="encoding" v-model="formValues.encoding" required="true">
	                        <option value="" disabled>{{ messages.get('common', 'common_default_select') }}</option>
	                        <option v-for="(option, index) in options.encoding" :value="option.value" :key="index">{{ option.label }}</option>
	                    </select>
                    </div>
            	</div>
                <div class="row ie-row">
                    <div class="colonne25">
                        <label class="etiquette" for="file">{{ currentType.msg.file }}</label>
                    </div>
                    <div class="colonne75">
                        <input name="file" type="file" @change="getFile" required />
                    </div>
                </div>
            </template>
        </settings-form>
    </div>
    <settings-form v-else :settings="currentType.settings" :msg="currentType.msg" :form-values="formValues"></settings-form>
</template>


<script>
import settingsForm from '@importexport/common/settingsForm.vue';
export default {
  components: { settingsForm },
    props : {
        currentType : {
            'type' : Object
        },
        formValues : {
            'type' : Object
        },
        baseParameters : {
            'type' : Object
        },
        execution : {
            'type' : Boolean,
            default : function() {
                return false;
            }
        }
    },
    data : function() {
    	return {
    		options : {}
    	}
    },
    created : async function() {
    	let response = await this.ws.get('Sources', 'callback/getEncodingsList');
        if(!response.error) {
            this.$set(this.options, 'encoding', response);
            if(!this.formValues.encoding) {
            	if(this.baseParameters !== undefined) {
            		this.formValues.encoding = this.baseParameters.defaultEncoding;
            	}
            }
        }
    },
    methods : {
        getFile : async function(e) {
            const file = e.target.files[0];
            const reader = new FileReader();

            reader.readAsText(file, this.formValues.encoding);

            reader.onload = (event) => {
                try {
                    this.$set(this.formValues, 'file', btoa(event.target.result));
                } catch (error) {
                    this.notif.error(this.currentType.msg.readError + " : " + error);
                    e.srcElement.value = '';
                }
            };
            reader.onerror = function(event) {
                this.notif.error(this.currentType.msg.readError + " : " + error);
                e.srcElement.value = '';
            };
        }
    }
}
</script>