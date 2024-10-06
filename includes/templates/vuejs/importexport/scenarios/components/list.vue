<template>
	<div>
		<div id="list">
			<table>
				<thead>
					<tr>
						<th>
							{{ messages.get('importexport', 'ie_scenario_name') }}
						</th>
						<th>
							{{ messages.get('importexport', 'ie_scenario_comment') }}
						</th>
						<th>
							{{ messages.get('importexport', 'ie_actions') }}
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(scenario, index) in list" :key="index">
						<td>{{ scenario.scenarioName }}</td>
						<td>{{ scenario.scenarioComment }}</td>
						<td>
							<button type="button" class="bouton" @click="edit(scenario.id)">{{ messages.get('common', 'edit') }}</button>
							<button type="button" class="bouton" @click="duplicate(scenario.id)">{{ messages.get('common', 'common_duplicate') }}</button>
							<button type="button" class="bouton" @click="execute(scenario.id)">{{ messages.get('common', 'execute') }}</button>
							<button type="button" class="bouton" @click="export_scenario(scenario.id)">{{ messages.get('common', 'export') }}</button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="add">
			<button type="button" class="bouton" @click="add()">{{ messages.get('importexport', 'ie_scenario_add') }}</button>
		</div>
	</div>
</template>

<script>
	export default {
		props: {
			list : {
				'type' : Array
			}
		},
		methods: {
			add: function () {
		        document.location = './import_export.php?categ=scenarios&action=edit';
		    },
			edit: function (id) {
		        document.location = './import_export.php?categ=scenarios&action=edit&id='+id;
		    },
		    duplicate : async function(id) {
				let response = await this.ws.post('scenarios', 'duplicate', {id : id});
				if(!response.error) {
					this.$set(this.list, this.list.length, response);
				} else {
					this.notif.error(this.messages.get('common', response.errorMessage));
				}
			},
		    execute: async function (id) {
				document.location = './import_export.php?categ=scenarios&action=execute&id='+id;
		    },
		    export_scenario: function (id) {
		        document.location = './import_export.php?categ=scenarios&action=export&id='+id;
		    },
		}
	}
</script>