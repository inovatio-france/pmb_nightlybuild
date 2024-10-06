<template>
	<div>
		<div id="entitiesAdd" class='row ie-row'>
			<div class="colonne25">
				<label class='etiquette' for='entityAddType'>{{ messages.get('importexport', 'ie_entities_types') }} :</label>
			</div>
			<div class="colonne75">
				<select id='entitiesTypes' name='entitiesTypes' v-model='entity.entityType'>
						<option value='' disabled>{{ messages.get('importexport', 'ie_entities_types_empty_value') }}</option>
						<option v-for='entityType in entitiesTypes' :value='entityType.value'>{{ entityType.msg.name }}</option>
					</select>
					<button type='button' class='bouton' @click='addEntity' :disabled='entity.entityType == ""'>
						<i class="fa fa-plus" aria-hidden="true"></i>
					</button>
			</div>
		</div>
		<div id="entitiesList">
			<fieldset class="ie-fieldset">
	            <legend>{{ messages.get('importexport', 'ie_entities_list') }}</legend>
	            <ul>
	                <li v-for="(entity, index) in sortedEntities" :key="index">
	                    <span @click="edit(entity)" :class="currentEntity.id == entity.id ? 'active' : ''" style="cursor:pointer">{{ getEntityTypeLabel(entity.entityType) }}</span>
	                    <button type="button" class="bouton" :value="entity.id" @click="remove(entity.id)" :title="messages.get('common', 'remove')">
	                        <i class="fa fa-times"></i>
	                    </button>
	                </li>
	                <li v-if="sortedEntities.length === 0">
	                    <p>{{ messages.get('importexport', 'ie_profile_entities_empty') }}</p>
	                </li>
	            </ul>
<!-- 	            <button type="button" @click="fetchData(false)" :class="eventActive.id == 0 ? 'disabled' : ''"  :title="messages.get('dsi', 'add')"> -->
<!-- 	                <i class="fa fa-plus"></i> -->
<!-- 	            </button> -->
	        </fieldset>
	        <fieldset class="ie-fieldset" v-if="currentEntity.id">
	        	<legend class="dsi-legend-setting">{{ getEntityTypeLabel(currentEntity.entityType) }}</legend>
	        	<entity-edit-form
	                v-if="currentEntity"
	                :entity="currentEntity"
	                :id-profile="idProfile"
	                :entity-type-fields="entityTypeFields">
	            </entity-edit-form>
	        </fieldset>
		</div>
	</div>
</template>

<script>
	import FormModal from '@/common/components/FormModal.vue';
	import entityEditForm from '@importexport/profilesImportEntities/entityEditForm.vue';

	export default {
		components : {
			FormModal,
			entityEditForm
		},
		props: {
			entities : {
				'type' : Array
			},
			entitiesTypes : {
				'type' : Array
			},
			idProfile : {
				'type' : Number
			}
		},
		created: function() {
			this.entity.numProfile = this.idProfile;
			this.initEntities();
		},
		computed: {
			sortedEntities: function() {
				return this.entities.sort((a,b) => a.label > b.label);
			},
			entityTypeFields : function() {
				let entityType = this.entitiesTypes.find(el => el.value == this.currentEntity.entityType );
				if (entityType !== undefined) {
					return entityType.fields;
				}
				return new Array();
			}
		},
		data: function() {
			return {
				entity : {
					id : 0,
					entityType : '',
					numProfile : 0
				},
				currentEntity : {},
				indexEditForm : 0
			}
	    },
		methods: {
			edit: function (entity) {
				this.currentEntity = entity;
		    },
		    save : async function(entity) {
				let response = await this.ws.post('profilesImportEntities', 'save', entity);
				if(!response.error) {
					this.notif.info(this.messages.get('common', 'success_save'));
					return response;
				}
				return false;
			},
			remove: async function (id) {
				if(confirm(this.messages.get('importexport', 'ie_profile_import_entity_remove_confirm'))) {
					let remove = await this.ws.post('profilesImportEntities', 'remove', { id : id });
					if(!remove.error) {
						this.notif.info(this.messages.get('common', 'success_delete'));
						let i = this.entities.findIndex(i => i.id == id);
						if(i != -1) {
							this.entities.splice(i, 1);
						}
					} else {
						this.notif.error(this.messages.get('common', 'failed_delete'));
					}
				}
		    },
		    initEntities: function() {
// 				this.$set(this, 'entityActive', {});
// 				this.$set(this, 'eventActive', result[1]);
			},
	        addEntity: async function() {
	        	if(this.idProfile) {
	        		let response = await this.ws.post('profiles_import_entities', 'save', this.entity);
	        		if(!response.error) {
	        			this.notif.info(this.messages.get('common', 'success_save'));
	        			this.entities.push(response);
	        		}
	        	} else {
					const entity = Object.assign({}, this.entity);
					this.entity = this.getEmptyEntity();
	        		this.entities.push(entity);
	        	}
	        },
	        getEmptyEntity: function() {
				return {
					id : 0,
					entityType : '',
					numProfile : 0
				};
			},
			getEntityTypeLabel: function(entityType) {
	        	let entity = this.entitiesTypes.find(element => element.value == entityType);
	        	if(entity && entity.msg) {
					return entity.msg.name;
				}
	        	return "";
	        },
		}
	}
</script>