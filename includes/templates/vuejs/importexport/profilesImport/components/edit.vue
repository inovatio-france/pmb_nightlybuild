<template>
    <div>
		<form id="profileForm" @submit.prevent="submit($event)">
			<div class='row ie-row'>
				<div class='colonne25'>
					<label for='profileName'>{{ messages.get("importexport", "ie_profile_name") }}</label>
				</div>
				<div class='colonne75'>
					<input type='text' id='profileName' class='saisie-50em' v-model='profile.profileName' required />
				</div>
			</div>
			<div class='row ie-row'>
				<div class='colonne25'>
					<label for='profileComment'>{{ messages.get("importexport", "ie_profile_comment") }}</label>
				</div>
				<div class='colonne75'>
					<textarea id='profileComment' v-model='profile.profileComment' cols='62' rows='5' ></textarea>
				</div>
			</div>
			<div class='row ie-row'>
				<div class='colonne25'>
					<label for='profileType'>{{ messages.get("importexport", "ie_profile_entities") }}</label>
				</div>
				<div class='colonne75'>
					<input type='radio' id='profileTypeAllEntities' v-model='profile.profileType' value='all_entities' />
					<label for='profileTypeAllEntities'>{{ messages.get("importexport", "ie_profile_import_all_entities") }}</label>
					<br />
					<input type='radio' id='profileTypeSelectedEntities' v-model='profile.profileType' value='selected_entities' />
					<label for='profileTypeSelectedEntities'>{{ messages.get("importexport", "ie_profile_import_selected_entities") }}</label>
				</div>
			</div>
			<entities-list :entities="profile.entities" :entities-types="entitiesTypes" :id-profile="profile.id">
			</entities-list>
			<div class="row ie-row">
				<div class='left'>
					<input type="button" class="bouton" :value="messages.get('common', 'cancel')" @click='cancel'>
					<input type="submit" class="bouton" :value="messages.get('common', 'submit')">
				</div>
				<div class='right' v-if='profile.id'>
					<input type="button" class="bouton" :value="messages.get('common', 'remove')" @click='remove'>
				</div>
			</div>
		</form>
	</div>
</template>

<script>
	import entitiesList from "./entitiesList.vue";

    export default {
    	props: {
			profile : {
				'type' : Object
			},
			entitiesTypes : {
				'type' : Array
			}
		},
		components: {
			entitiesList
		},
		methods: {
			cancel: function() {
				document.location = './import_export.php?categ=profiles_import';
			},
			submit: async function(e) {
				let response = await this.ws.post('profilesImport', 'save', this.profile );
				if(! response.error) {
					if(this.profile.id) {
						document.location = './import_export.php?categ=profiles_import';
					} else {
						document.location = './import_export.php?categ=profiles_import&action=edit&id='+response.id;
					}
				}
			},
			remove: async function() {
				let response = await this.ws.post('profilesImport', 'remove', {id : this.profile.id });
				if(!response.error) {
					document.location = './import_export.php?categ=profiles_import';
				} else {
					this.notif.error(this.messages.get('common', 'failed_delete'));
				}
			}

		}
	}
</script>