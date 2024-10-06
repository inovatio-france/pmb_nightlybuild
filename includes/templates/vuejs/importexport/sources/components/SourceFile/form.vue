<template>
    <div id="source-file-form">
        <div class="row ie-row">
            <div class="colonne25">
                <label class="etiquette">
                    {{ currentType.msg.fileSystemLabel }}
                </label>
            </div>
            <div class="colonne75">
                <select v-model="formValues.fileSystem" :disabled="execution">
                    <option value="" disabled>{{ currentType.msg.selectFileSystem }}</option>
                    <option v-for="(fileSystem, index) in currentType.fileSystems" :key="index" :value="fileSystem.namespace">{{ fileSystem.msg.name }}</option>
                </select>
            </div>
        </div>
        <div class="row ie-row" v-if="execution">
            <component v-if="fileSystemSettingsComponent"
                :is="fileSystemSettingsComponent"
                :current-type="currentFileSystem"
                :form-values="contextParameters[formValues.fileSystem]"
                :base-parameters="formValues[formValues.fileSystem]"
                :execution="execution"></component>
            <settings-form v-else
                :settings="currentFileSystem.contextParameters"
                :msg="currentFileSystem.msg"
                :form-values="contextParameters[formValues.fileSystem]"></settings-form>
        </div>
        <div class="row ie-row" v-else-if="formValues.fileSystem && Object.keys(formValues[formValues.fileSystem]).length">
            <component v-if="fileSystemSettingsComponent"
                :is="fileSystemSettingsComponent"
                :current-type="currentFileSystem"
                :form-values="formValues[formValues.fileSystem]"></component>
            <settings-form v-else :settings="currentFileSystem.settings" :msg="currentFileSystem.msg" :form-values="formValues[formValues.fileSystem]"></settings-form>
        </div>
    </div>
</template>

<script>
import settingsForm from '@importexport/common/settingsForm.vue';

export default {
    props : {
        currentType : {
            'type' : Object
        },
        formValues : {
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
    components: {
        settingsForm
    },
    created : function() {
        if(this.execution) {
            this.checkShowForm();
            this.initSourceFileContextParameters();
        } else {
            this.initSourceFileSettings();
        }
    },
    methods : {
        initSourceFileSettings : function() {
            if(! this.formValues.fileSystem) {
                this.$set(this.formValues, 'fileSystem', "");
            }

        },
        initSourceFileContextParameters : function() {
            this.$set(this.contextParameters, this.formValues.fileSystem, {});
            this.currentFileSystem.contextParameters.forEach(setting => {
                if(!this.contextParameters[this.formValues.fileSystem][setting.name]){
                    this.$set(this.contextParameters[this.formValues.fileSystem], setting.name, setting.value);
                }
            });
        },
        checkShowForm : function() {
            if(this.formValues.fileSystem) {
                let fileSystem = this.currentType.fileSystems.find(fileSystem => fileSystem.namespace == this.formValues.fileSystem);
                this.$emit("showform", fileSystem.contextParameters.length > 0);
            }
        }
    },
    watch : {
        'formValues.fileSystem' : function(newVal, oldVal) {
            if(! this.currentFileSystem) {
                return;
            }
            if(this.formValues[oldVal]) {
                this.$delete(this.formValues, oldVal);
            }
            this.$set(this.formValues, newVal, {});
            this.currentFileSystem.settings.forEach(setting => {
                if(!this.formValues[newVal][setting.name]){
                    this.$set(this.formValues[newVal], setting.name, setting.value);
                }
            })
        }
    },
    computed : {
        fileSystemSettingsComponent : function(){
            if(this.currentFileSystem) {
                try {
                    require('@importexport/sources/components/' + this.currentFileSystem.type + '/form.vue');
                    return () => import('@importexport/sources/components/' + this.currentFileSystem.type + '/form.vue');
                } catch (e) {
                    console.info(e)
                }
            }
            return null;
        },
        currentFileSystem : function() {
            if(this.formValues.fileSystem) {
                let i = this.currentType.fileSystems.findIndex(fileSystem => fileSystem.namespace == this.formValues.fileSystem);
                if(i >= 0) {
                    return this.currentType.fileSystems[i];
                }
            }
            return null;
        }
    }
}
</script>