<template>
    <div class="row ie-row ie-transformers-list-section">
        <div class="ie-transformers-add">
            <div class="colonne25">
                <label class="etiquette" for="sourceTransformers">
                    {{ messages.get('importexport', 'ie_sources_transformers') }}
                </label>
            </div>
            <div class="colonne75">
                <select id="sourceTransformers" v-model="formTransformer.namespace">
                    <option value="" disabled>{{ messages.get('importexport', 'ie_sources_transformers_unselected') }}</option>
                    <option v-for="transformer in availableTransformers" :value="transformer.namespace" :key="transformer.namespace">{{ transformer.msg.name }}</option>
                </select>
                <label class="visually-hidden" for="sourceTransformersDesc">
                    {{ messages.get('importexport', 'ie_sources_transformers_desc') }}
                </label>
                <input id="sourceTransformersDesc" type="text" v-model="formTransformer.description">
                <button type='button' class='bouton' @click="addTransformer">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </button>
            </div>
        </div>
        <div class="row ie-row ie-transformers-list-container">
            <ul class="ie-transformers-list">
                <li v-for="(transformer, index) in transformers" :key="index" class="ie-transformers-list-item">
                    <div class="ie-transformers-list-item-content">
                        <div>
                            <button :disabled="index <= 0" type="button" class="bouton"
		                        @click="moveUp(index)" :title=" messages.get('importexport', 'ie_move_up')">
		                        <i class="fa fa-arrow-up" aria-hidden="true"></i>
		                    </button>
		                    <button :disabled="index >= transformers.length - 1" type="button" class="bouton"
		                        @click="moveDown(index)" :title=" messages.get('importexport', 'ie_move_down')">
		                        <i class="fa fa-arrow-down" aria-hidden="true"></i>
		                    </button>
                        </div>
                        <div class="ie-transformers-list-item-element">
                            <b>{{ getTransformerLabel(transformer.namespace) }}</b>
                            <div>{{ transformer.description }}</div>
                        </div>
                        <span class="ie-transformers-list-buttons">
                            <button v-if="getTransformerSettings(transformer.namespace).length" type="button" class="bouton" @click="toggleSettings(index)">
                                <i :class="showSettings[index] ? 'fa fa-chevron-up' : 'fa fa-chevron-down'" aria-hidden="true"></i>
                            </button>
                            <button type="button" class="bouton" @click="remove(index)">
                                <i class="fa fa-times" aria-hidden="true"></i>
                            </button>
                        </span>
                    </div>
                    <div class="ie-transformers-list-item-settings">
                        <settings-form v-show="showSettings[index]" :settings="getTransformerSettings(transformer.namespace)" :msg="getTransformer(transformer.namespace).msg" :form-values="transformer.settings"></settings-form>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import settingsForm from '../common/settingsForm.vue';
export default {
  components: { settingsForm },
    props : {
        transformers : {
            'type' : Array
        },
        availableTransformers : {
            'type' : Array
        }
    },
    data : function() {
        return {
            formTransformer : {
                namespace : "",
                description : "",
                settings : {}
            },
            showSettings : []
        }
    },
    created : function() {
        for(let i in this.transformers) {
            this.showSettings[i] = false;
        }
    },
    methods : {
        addTransformer : function() {
            let transformerSettings = this.getTransformerSettings(this.formTransformer.namespace);
            if(transformerSettings) {
                for(let setting of transformerSettings) {
                    this.formTransformer.settings[setting.name] = setting.value;
                }
            }
            this.transformers.push(this.formTransformer);
            this.showSettings.push(false);
            this.formTransformer = {
                namespace : "",
                description : "",
                settings : {}
            };
        },
        getTransformer : function(namespace) {
            let transformer = this.availableTransformers.find(t => t.namespace == namespace);
            if(transformer) {
                return transformer;
            }
            return {};
        },
        getTransformerLabel : function(namespace) {
            let transformer = this.getTransformer(namespace);
            if(transformer.msg) {
                return transformer.msg.name;
            }
            return "";
        },
        getTransformerMessages : function(namespace) {
            let transformer = this.getTransformer(namespace);
            if(transformer.msg) {
                return transformer.msg;
            }
            return {};
        },
        getTransformerSettings : function(namespace) {
            let transformer = this.getTransformer(namespace);
            if(transformer.settings) {
                return transformer.settings;
            }
            return {};
        },
        remove : function(i) {
            if(confirm(this.messages.get('common', 'confirm_delete'))) {
                this.transformers.splice(i, 1);
            }
        },
        toggleSettings : function(i) {
            if(this.showSettings[i] !== undefined) {
                this.$set(this.showSettings, i, !this.showSettings[i]);
            }
        },
        moveUp: function(i) {
            if(i > 0) {
                const temp = this.transformers[i];
                const tempShowSettings = this.showSettings[i];

                this.$set(this.transformers, i, this.transformers[i - 1]);
                this.$set(this.showSettings, i, this.showSettings[i - 1]);
                this.$set(this.transformers, i - 1, temp);
                this.$set(this.showSettings, i - 1, tempShowSettings);
            }
        },
        moveDown: function(i) {
            if(i < this.transformers.length-1) {
                const temp = this.transformers[i];
                const tempShowSettings = this.showSettings[i];

                this.$set(this.transformers, i, this.transformers[i + 1]);
                this.$set(this.showSettings, i, this.showSettings[i + 1]);
                this.$set(this.transformers, i + 1, temp);
                this.$set(this.showSettings, i + 1, tempShowSettings);
            }
        }
    }
}
</script>