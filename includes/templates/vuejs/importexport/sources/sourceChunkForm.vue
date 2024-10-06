<template>
    <div>
        <div class="row ie-row">
            <div class="colonne25">
                <label class="etiquette" for="sourceEntryFormat">
                    {{ messages.get('importexport', 'ie_sources_entry_chunk') }}
                </label>
            </div>
            <div class="colonne75">
                <select id="sourceEntryFormat" v-model="sourceSettings.entryFormat">
                    <option value="" disabled>{{ messages.get('importexport', 'ie_sources_unselected_chunk') }}</option>
                    <option v-for="(format, index) in currentType.formats" :key="index" :value="format.namespace">{{ format.msg.name }}</option>
                </select>
            </div>
        </div>
        <settings-form v-if="selectedFormat" :settings="selectedFormat.settings" :msg="selectedFormat.msg" :form-values="sourceSettings[selectedFormat.namespace]"></settings-form>
    </div>
</template>

<script>
import settingsForm from '@importexport/common/settingsForm.vue';
export default {
    props : {
        sourceSettings : {
            'type' : Object
        },
        currentType : {
            'type' : Object
        }
    },
    components: { settingsForm },
    computed : {
        selectedFormat : function() {
            if(this.sourceSettings.entryFormat) {
                return this.currentType.formats.find(format => format.namespace == this.sourceSettings.entryFormat);
            }
            return null;
        }
    },
    data : function() {
        return {
            prevEntryFormat : ""
        }
    },
    watch : {
        "sourceSettings.entryFormat" : function(newVal, oldVal) {
            //Petit systeme pour gerer la fenetre de confirmation
            if((this.prevEntryFormat == newVal) && (this.prevEntryFormat != "")) {
                return;
            }
            this.prevEntryFormat = oldVal;
            if(oldVal != "") {
                if(! confirm(this.messages.get('importexport', 'ie_source_change_entryformat_confirm'))) {
                    this.$set(this.sourceSettings, "entryFormat", oldVal);
                    return;
                }
            }
            this.prevEntryFormat = "";
            //On supprime l'ancien parametrage et les transformers associes
            if(this.sourceSettings[oldVal]) {
                this.$delete(this.sourceSettings, oldVal);
            }
            if(this.sourceSettings.transformers) {
                this.$set(this.sourceSettings, "transformers", []);
            }
            //On set les parametres par defaut du nouveau format
            this.$set(this.sourceSettings, newVal, {});
            this.selectedFormat.settings.forEach(setting => {
                if(!this.sourceSettings[newVal][setting.name]){
                    this.$set(this.sourceSettings[newVal], setting.name, setting.value);
                }
            });
        }
    }
}
</script>