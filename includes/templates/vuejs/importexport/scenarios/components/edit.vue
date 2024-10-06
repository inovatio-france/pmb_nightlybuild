<template>
	<form id="scenarioForm" @submit.prevent="submit">
		<div class='row ie-row'>
			<div class='colonne25'>
				<label for='scenarioName'>{{ messages.get("importexport", "ie_scenario_name") }}</label>
			</div>
			<div class='colonne75'>
				<input type='text' id='scenarioName' name='scenarioName' class='saisie-50em' v-model='scenario.scenarioName' required />
			</div>
		</div>
		<div class='row ie-row'>
			<div class='colonne25'>
				<label for='scenarioComment'>{{ messages.get("importexport", "ie_scenario_comment") }}</label>
			</div>
			<div class='colonne75'>
				<textarea id='scenarioComment' name='scenarioComment' v-model='scenario.scenarioComment' cols='62' rows='5' ></textarea>
			</div>
		</div>
		<sources-list :sources="scenario.sources" :sources-types="sourcesTypes" :id-scenario="scenario.id">
		</sources-list>
		<steps-list :steps="scenario.steps" :steps-types="stepsTypes" :id-scenario="scenario.id" :sources="scenario.sources">
		</steps-list>
		<div class="row ie-row">
			<div class='left'>
				<input type="button" class="bouton" :value="messages.get('common', 'cancel')" @click='cancel'>
				<input type="submit" class="bouton" :value="messages.get('common', 'submit')">
			</div>
			<div class='right' v-if='scenario.id'>
				<input type="button" class="bouton" :value="messages.get('common', 'remove')" @click='remove'>
			</div>
		</div>
	</form>
</template>

<script>
	import sourcesList from "./sourcesList.vue";
	import stepsList from "./stepsList.vue";

    export default {
    	props: {
			scenario : {
				'type' : Object
			},
			sourcesTypes : {
				'type' : Array
			},
			stepsTypes : {
				'type' : Array
			}
		},
		components: {
			sourcesList,
			stepsList
		},
		methods: {
			cancel: function() {
				document.location = './import_export.php?categ=scenarios';
			},
			submit: async function() {
				let response = await this.ws.post('scenarios', 'save', this.scenario);
				if(! response.error) {
					if(this.scenario.id) {
						document.location = './import_export.php?categ=scenarios';
					} else {
						document.location = './import_export.php?categ=scenarios&action=edit&id='+response.id;
					}
				}
			},
			remove: async function() {
				let response = await this.ws.post('scenarios', 'remove', {id : this.scenario.id });
				if(!response.error) {
					document.location = './import_export.php?categ=scenarios';
				} else {
					this.notif.error(this.messages.get('common', 'failed_delete'));
				}
			}

		}
	}
</script>