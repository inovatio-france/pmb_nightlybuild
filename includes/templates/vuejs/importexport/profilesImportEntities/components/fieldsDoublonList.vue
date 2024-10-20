<template>
	<div>
		<div id="fieldsCreationDoublon" class='row ie-row'>
			<label class="visually-hidden" for='fieldsDoublonList'>{{ messages.get('importexport', 'ie_fields_list') }}</label>
			<select id='fieldsDoublonList' name='fieldsDoublonList' v-model='field.fieldCode'>
				<option value='' disabled>{{ messages.get('importexport', 'ie_fields_empty_value') }}</option>
				<option v-for='(entityTypeField, index) in entityTypeFields' :value='entityTypeField.value' :key="index">{{ entityTypeField.label }}</option>
			</select>
			<button type='button' class='bouton' @click='addField' :disabled='field.code == ""'>
				<i class="fa fa-plus" aria-hidden="true"></i>
			</button>
		</div>
		<div id="entityFieldsDoublonList">
			<table class="uk-table uk-table-small uk-table-striped uk-table-middle">
				<caption>{{ messages.get('importexport', 'ie_fields_doublon_list') }}</caption>
				<thead>
					<tr>
						<th>
							{{ messages.get('importexport', 'ie_operator') }}
						</th>
						<th>
							{{ messages.get('importexport', 'ie_field') }}
						</th>
						<th>
							{{ messages.get('importexport', 'ie_actions') }}
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(field, index) in fields" :key="index">
						<td>
							<select v-if="index" :id="'ie_profile_import_field_'+ mode +'_'+field.fieldCode+'_operator'" v-model='field.fieldOperator'>
								<option value=''></option>
								<option value='AND'>AND</option>
								<option value='OR'>OR</option>
							</select>
						</td>
						<td>{{ getFieldLabel(field.fieldCode) }}</td>
						<td>
							<button type="button" class="bouton" @click="edit(field)">{{ messages.get('common', 'edit') }}</button>
							<button type="button" class="bouton" @click="remove(field.fieldCode)">{{ messages.get('common', 'remove') }}</button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<form-modal
			class="ie-modal"
			ref="modal"
			:title="messages.get('importexport', 'ie_profile_import_modal_title_edit_field')">
			<fields-edit-form
				v-if="currentField"
				:field="currentField"
				@save="save($event)"
				:key="indexEditForm"></fields-edit-form>
		</form-modal>
	</div>
</template>

<script>
	import FormModal from '../../../common/components/FormModal.vue';
	import fieldsEditForm from './fieldsEditForm.vue';
	export default {
		components : {
			FormModal,
			fieldsEditForm
		},
		props: {
			mode : {
				'type' : String
			},
			fields : {
				'type' : Array
			},
			entityTypeFields : {
				'type' : Array
			},
			idProfile : {
				'type' : Number
			}
		},
		created: function() {
		},
		computed: {
		},
		data: function() {
			return {
				field : {
					fieldCode : '',
					fieldLabel : '',
					fieldOperator : ''
				},
				currentField : {},
				indexEditForm : 0
			}
	    },
		methods: {
			edit: function (field) {
				this.currentField = field;
				//Permet de faire le rerender de la modal avec les nouvelles donnees
				this.indexEditForm++;
				this.$refs.modal.show();
		    },
		    cancel : function(field) {
		    	this.$refs.modal.close();
		    },
		    save : async function(field) {
		    	let response = await this.ws.post('profilesImportEntities/'+this.idProfile, 'saveField', field);
				if(!response.error) {
					this.notif.info(this.messages.get('common', 'success_save'));
					this.$refs.modal.close();
					return response;
				}
				return false;
			},
			remove: async function (code) {
				let i = this.fields.findIndex(element => element.fieldCode == code);
				if(i != -1) {
					this.fields.splice(i, 1);
				}
		    },
	        addField: async function() {
				const field = Object.assign({}, this.field);
        		this.fields.push(field);
				this.field = this.getEmptyField();
	        },
			getFieldLabel: function(fieldCode) {
	        	let field = this.entityTypeFields.find(element => element.value == fieldCode);
	        	if(field && field.label) {
					return field.label;
				}
	        	return "";
	        },
	        getEmptyField: function() {
				return {
					fieldCode : '',
					fieldLabel : '',
					fieldOperator : ''
				};
			}
		}
	}
</script>