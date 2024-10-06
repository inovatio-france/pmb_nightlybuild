<template>
    <div class="form-contenu" v-if="showForm">
        <div class="row ie-row" v-if="!execution">
            <div class="colonne25">
                <label class="etiquette" for="sourceName">
                    {{ messages.get('importexport', 'ie_source_name') }}
                </label>
            </div>
            <div class="colonne75">
                <input id="sourceName" type="text" v-model="source.sourceName" />
            </div>
        </div>
         <div class="row ie-row">
            <div class="colonne25">
                <label class="etiquette" for="sourceComment">
                    {{ messages.get('importexport', 'ie_source_comment') }}
                </label>
            </div>
            <div class="colonne75">
                <textarea id="sourceComment" v-model="source.sourceComment" :disabled="execution"></textarea>
            </div>
        </div>
        <div class="row ie-row">
            <div class="colonne25">
                <label class="etiquette" for="sourceType">
                    {{ messages.get('importexport', 'ie_source_type') }}
                </label>
            </div>
            <div class="colonne75">
                <input :value="currentSourceType.msg.name" disabled />
            </div>
        </div>
        <div v-if="execution">
            <h3>{{ messages.get('common', 'parameters') }}</h3>
            <component v-if="sourceSettingsComponent"
                :is="sourceSettingsComponent"
                :current-type="currentSourceType"
                :form-values="source.sourceSettings"
                :execution="execution"
                :context-parameters="contextParameters"
                @showform="showForm = $event"></component>
            <settings-form v-else :settings="currentSourceType.contextParameters" :msg="currentSourceType.msg" :form-values="contextParameters"></settings-form>
            <!--<source-transformers-list v-if="currentFormat && currentFormat.transformers && currentFormat.transformers.length"
                :transformers="source.sourceSettings.transformers"
                :available-transformers="currentFormat.transformers" />-->
        </div>
        <div v-else>
            <h3>{{ messages.get('common', 'parameters') }}</h3>
            <component v-if="sourceSettingsComponent" 
            	:is="sourceSettingsComponent" 
            	:current-type="currentSourceType" 
            	:form-values="source.sourceSettings"></component>
            <settings-form v-else :settings="currentSourceType.settings" :msg="currentSourceType.msg" :form-values="source.sourceSettings"></settings-form>
            <source-chunk-form v-if="currentSourceType.formats && currentSourceType.formats.length" :source-settings="source.sourceSettings" :current-type="currentSourceType"></source-chunk-form>
            <source-transformers-list v-if="currentFormat && currentFormat.transformers && currentFormat.transformers.length"
                :transformers="source.sourceSettings.transformers"
                :available-transformers="currentFormat.transformers" />
            <div v-if="source.sourceSettings.entryFormat" class="row ie-row">
                <div class="row ie-row">
                    <label class="etiquette">
                        {{ messages.get('importexport', 'ie_source_ontology_entities') }}
                    </label>
                </div>
                <div class="row ie-row">
                    <fieldset class="ie-fieldset">
                        <div class="row ie-row" v-for="(input, index) in source.sourceSettings.ontologyEntities" :key="index">
                            <input type="text" v-model="source.sourceSettings.ontologyEntities[index].value" class="saisie-30em" />
                            <button type="button" class="bouton" @click="deleteOntologyEntity(index)" :title="messages.get('common', 'remove')">
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </button>
                            <button v-if="index == source.sourceSettings.ontologyEntities.length - 1" @click="addOntologyEntity(index, $event)"
                                type="button" class="bouton" :title="messages.get('common', 'more_label')">
                                <i class="fa fa-plus" aria-hidden="true"></i>
                            </button>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import sourceChunkForm from './sourceChunkForm.vue';
import settingsForm from '@importexport/common/settingsForm.vue';
import sourceTransformersList from './sourceTransformersList.vue';

export default {
    components: {
        sourceChunkForm,
        settingsForm,
        sourceTransformersList,
    },
    props : {
        source : {
            'type' : Object
        },
        currentSourceType : {
            'type' : Object
        },
        execution : {
            'type' : Boolean,
            default : function() {
                return false;
            }
        },
        contextParameters : {
            'type' : Object,
            default : function() {
                return {};
            }
        }

    },
    data : function() {
        return {
            showForm : true
        }
    },
    created: function() {
        if(this.execution) {
            this.initSourceContextParameters();
        } else {
            this.initSourceSettings();
        }
    },
    computed : {
        currentFormat : function(){
            if(this.currentSourceType && this.currentSourceType.formats){
                return this.currentSourceType.formats.find(format => format.namespace == this.source.sourceSettings.entryFormat);
            }
            return null;
        },
        sourceSettingsComponent : function(){
            if(this.currentSourceType.type) {
                try {
                    require('./components/' + this.currentSourceType.type + '/form.vue');
                    return () => import('./components/' + this.currentSourceType.type + '/form.vue');
                } catch (e) {
                    console.info(e)
                }
            }
            return null;
        }
    },
    methods:{
        initSourceSettings : function () {
            if(! this.source.sourceSettings){
                this.$set(this.source, 'sourceSettings', {});
            }
            if(! this.source.sourceSettings.entryFormat){
                this.$set(this.source.sourceSettings, 'entryFormat', '');
            }
            if(! this.source.sourceSettings.outFormat){
                this.$set(this.source.sourceSettings, 'outFormat', '');
            }
            if(! this.source.sourceSettings.transformers) {
                this.$set(this.source.sourceSettings, 'transformers', []);
            }
            if(! this.source.sourceSettings.ontologyEntities) {
                this.$set(this.source.sourceSettings, 'ontologyEntities', [{value : ''}]);
            }
            this.currentSourceType.settings.forEach(setting => {
                if(!this.source.sourceSettings[setting.name]){
                	if(setting.multiple) {
                		let values = new Array();
                		values.push({value : ''});
                		this.$set(this.source.sourceSettings, setting.name, values);
                	} else {
                		this.$set(this.source.sourceSettings, setting.name, setting.value);
                	}
                }
            });

        },
        initSourceContextParameters : function () {
            this.currentSourceType.settings.forEach(setting => {
                if(!this.contextParameters[setting.name]){
                	if(setting.multiple) {
                		let values = new Array();
                		values.push({value : ''});
                		this.$set(this.contextParameters, setting.name, values);
                	} else {
                		this.$set(this.contextParameters, setting.name, setting.value);
                	}
                }
            });
        },
        deleteOntologyEntity: function (index) {
            if (this.source.sourceSettings.ontologyEntities.length > 1) {
            	this.source.sourceSettings.ontologyEntities.splice(index, 1);
            } else {
                this.source.sourceSettings.ontologyEntities[index].value = '';
            }
        },
        addOntologyEntity: function (index, event) {
            this.source.sourceSettings.ontologyEntities.push({value : ''});
        },
        checkShowForm : function() {
            if(this.formValues.fileSystem && this.sourceSettingsComponent == null) {
                this.showForm = this.currentSourceType.contextParameters.length > 0;
            }
        }
    },
}
</script>
