<template>
    <form id="executionForm" @submit.prevent="execute">
		<div class='row ie-row'>
			<div class='colonne25'>
				<label for='scenarioName'>{{ messages.get("importexport", "ie_scenario_name") }}</label>
			</div>
			<div class='colonne75'>
				<input disabled type='text' id='scenarioName' name='scenarioName' class='saisie-50em' v-model='scenario.scenarioName' required />
			</div>
		</div>
		<div class='row ie-row'>
			<div class='colonne25'>
				<label for='scenarioComment'>{{ messages.get("importexport", "ie_scenario_comment") }}</label>
			</div>
			<div class='colonne75'>
				<textarea disabled id='scenarioComment' name='scenarioComment' v-model='scenario.scenarioComment' cols='62' rows='5' ></textarea>
			</div>
		</div>
        <h3>{{ messages.get('importexport', 'ie_scenario_execution_context_parameters') }}</h3>
        <fieldset v-for="(source, index) in scenario.sources" :key="index" v-show="showForm(source.id)" class="ie-fieldset">
            <legend>{{ source.sourceName }}</legend>
            <sources-edit-form :ref="'source-edit-form-' + source.id" :source="source" :current-source-type="getSourceType(source.sourceType)" :execution="true" :context-parameters="source.contextParameters"></sources-edit-form>
        </fieldset>
        <div class="row ie-row">
			<div class='left'>
				<input type="button" class="bouton" :value="messages.get('common', 'cancel')" @click='cancel'>
                <button class="bouton right" type="submit">{{ messages.get('common', 'execute') }}</button>
			</div>
		</div>
    </form>
</template>

<script>
import sourcesEditForm from '@importexport/sources/sourcesEditForm.vue';
export default {
    components: { sourcesEditForm },
    props : {
        scenario : {
            'type' : Object
        },
        sourcesTypes : {
            'type' : Array
        }
    },
    created : function() {
        this.initContextParameters();
    },
    methods: {
        execute: async function () {
            let response = await this.ws.post('scenarios', 'execute', this.scenario);
            if(!response.error) {
                this.notif.info(this.messages.get('common', 'successful_operation'));
            } else {
                this.notif.error(response.errorMessage);
            }
        },
        getSourceType : function(sourceNamespace) {
            let sourceType = this.sourcesTypes.find(sourceType => sourceType.namespace == sourceNamespace);
            if(sourceType) {
                return sourceType;
            }
            return null;
        },
        initContextParameters : function() {
            for(let source of this.scenario.sources) {
                this.$set(source, 'contextParameters', {});
            }
        },
        showForm : function(sourceId) {
            if(this.$refs['source-edit-form-' + sourceId]) {
                return this.$refs['source-edit-form-' + sourceId][0].showForm;
            }
            return true;
        },
        cancel: function() {
            document.location = './import_export.php?categ=scenarios';
        }
    }
}
</script>