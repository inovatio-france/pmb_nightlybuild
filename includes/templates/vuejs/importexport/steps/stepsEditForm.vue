<template>
    <div class="form-contenu">
        <div class="row ie-row">
            <div class="colonne25">
                <label class="etiquette" for="stepName">
                    {{ messages.get('importexport', 'ie_step_name') }}
                </label>
            </div>
            <div class="colonne75">
                <input id="stepName" type="text" v-model="step.stepName">
            </div>
        </div>
         <div class="row ie-row">
            <div class="colonne25">
                <label class="etiquette" for="stepComment">
                    {{ messages.get('importexport', 'ie_step_comment') }}
                </label>
            </div>
            <div class="colonne75">
                <textarea id="stepComment" v-model="step.stepComment"></textarea>
            </div>
        </div>
        <div v-if="currentStepType.settings.length && Object.keys(step.stepSettings).length">
            <h3>{{ messages.get('common', 'parameters') }}</h3>
            <component v-if="stepSettingsComponent" :is="stepSettingsComponent" :settings="currentStepType.settings" :msg="currentStepType.msg" :form-values="step.stepSettings"></component>
            <settings-form
                v-else
           		:settings="currentStepType.settings"
       			:msg="currentStepType.msg"
       			:form-values="step.stepSettings"
       			:sources="sources">
			</settings-form>
        </div>
    </div>
</template>

<script>
import settingsForm from '../common/settingsForm.vue';

export default {
    components: {
        settingsForm
    },
    props : {
        step : {
            'type' : Object
        },
        currentStepType : {
            'type' : Object
        },
        sources : {
        	'type' : Array
        }

    },
    created: function() {
        this.initStepSettings();
    },
    computed : {
        stepSettingsComponent : function(){
            if(this.step.stepType) {
                try {
                    require('./components/' + this.step.stepType + '/form.vue');
                    return () => import('./components/' + this.step.stepType + '/form.vue');
                } catch (e) {
                    console.info(e)
                }
            }
            return null;
        }
    },
    methods:{
        initStepSettings : function () {
            if(! this.step.stepSettings){
                this.step.stepSettings = {};
            }
            this.currentStepType.settings.forEach(setting => {
                if(!this.step.stepSettings[setting.name]){
                    this.step.stepSettings[setting.name] = setting.value;
                }
            });

        }
    }
}
</script>
