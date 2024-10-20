<template>
	<div>
		<div class='row ie-row'>
			<input type="radio" :id="'ie_profile_import_fields_'+ mode +'_default_action_all'" v-model="fields.defaultAction" value="all" />
			<label v-if="mode == 'Creation'" :for="'ie_profile_import_fields_'+ mode +'_default_action_all'">{{ messages.get("importexport", "ie_fields_creation_all") }}</label>
			<label v-if="mode == 'Replacement'" :for="'ie_profile_import_fields_'+ mode +'_default_action_all'">{{ messages.get("importexport", "ie_fields_replacement_all") }}</label>
		</div>
		<div class='row ie-row'>
			<input type="radio" :id="'ie_profile_import_fields_'+ mode +'_default_action_selection'" v-model="fields.defaultAction" value="selection" />
			<label v-if="mode == 'Creation'" :for="'ie_profile_import_fields_'+ mode +'_default_action_selection'">{{ messages.get("importexport", "ie_fields_creation_selection") }}</label>
			<label v-if="mode == 'Replacement'" :for="'ie_profile_import_fields_'+ mode +'_default_action_selection'">{{ messages.get("importexport", "ie_fields_replacement_selection") }}</label>
		</div>
		<div :id="'fields'+ mode + 'Add'" class="row ie-row">
			<label class="visually-hidden" :for="'fields'+ mode + 'List'">{{ messages.get('importexport', 'ie_fields_list') }}</label>
			<select :id="'fields'+ mode + 'List'" :name="'fields'+ mode + 'List'" v-model='field.fieldCode'>
				<option value='' disabled>{{ messages.get('importexport', 'ie_fields_empty_value') }}</option>
				<option v-for='(entityTypeField, index) in entityTypeFields' :value='entityTypeField.value' :key="index">{{ entityTypeField.label }}</option>
			</select>
			<button type='button' class='bouton' @click='addField' :disabled='field.fieldCode == ""'>
				<i class="fa fa-plus" aria-hidden="true"></i>
			</button>
		</div>
		<div :id="'entityFields' + mode + 'List'">
			<table class="uk-table uk-table-small uk-table-striped uk-table-middle">
				<caption v-if="mode == 'Creation'">{{ messages.get('importexport', 'ie_fields_creation_list') }}</caption>
				<caption v-if="mode == 'Replacement'">{{ messages.get('importexport', 'ie_fields_replacement_list') }}</caption>
				<thead>
					<tr>
						<th>
							{{ messages.get('importexport', 'ie_field') }}
						</th>
						<th>
							{{ messages.get('importexport', 'ie_operation') }}
						</th>
						<th>
							{{ messages.get('importexport', 'ie_default_value') }}
						</th>
						<th>
							{{ messages.get('importexport', 'ie_action') }}
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(field, index) in sortedFields" :key="index">
						<td>{{ getFieldLabel(field.fieldCode) }}</td>
						<td>
							<span v-if="mode == 'Creation'">
								<input type="checkbox" :id="'ie_profile_import_field_'+ mode +'_'+field.fieldCode+'_ignore'" v-model="field.fieldOperation.ignore" />
								<label class="etiquette" :for="'ie_profile_import_field_'+ mode +'_'+field.fieldCode+'_ignore'">{{ messages.get('importexport', 'ie_fields_ignore') }}</label>
							</span>
							<span v-if="mode == 'Creation'">
								<input type="checkbox" :id="'ie_profile_import_field_'+ mode +'_'+field.fieldCode+'_use_defaut_value'" v-model="field.fieldOperation.use_default_value" />
								<label class="etiquette" :for="'ie_profile_import_field_'+ mode +'_'+field.fieldCode+'_use_defaut_value'">{{ messages.get('importexport', 'ie_fields_use_default_value') }}</label>
							</span>
							<span v-if="mode == 'Replacement'">
								<input type="radio" :id="'ie_profile_import_field_'+ mode +'_'+field.fieldCode+'_ignore'" value="ignore" v-model="field.fieldOperation.change" required>
								<label class="etiquette" :for="'ie_profile_import_field_'+ mode +'_'+field.fieldCode+'_ignore'">{{ messages.get('importexport', 'ie_fields_ignore') }}</label>
							</span>
							<span v-if="mode == 'Replacement'">
								<input type="radio" :id="'ie_profile_import_field_'+ mode +'_'+field.fieldCode+'_replace'" value="replace" v-model="field.fieldOperation.change" required>
								<label class="etiquette" :for="'ie_profile_import_field_'+ mode +'_'+field.fieldCode+'_replace'">{{ messages.get('importexport', 'ie_fields_replace') }}</label>
							</span>
							<span v-if="mode == 'Replacement'">
								<input type="radio" :id="'ie_profile_import_field_'+ mode +'_'+field.fieldCode+'_add'" value="add" v-model="field.fieldOperation.change" required>
								<label class="etiquette" :for="'ie_profile_import_field_'+ mode +'_'+field.fieldCode+'_add'">{{ messages.get('importexport', 'ie_fields_add') }}</label>
							</span>
						</td>
						<td></td>
						<td>
							<button type="button" class="bouton" @click="remove(field.fieldCode)">{{ messages.get('common', 'remove') }}</button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script>

	export default {
		components : {
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
			sortedFields: function() {
				if (this.fields.length) {
					return this.fields.sort((a,b) => a.fieldLabel > b.fieldLabel);
				}
			}
		},
		data: function() {
			return {
				field : {
					fieldCode : '',
					fieldLabel : '',
					fieldOperation : {
						ignore : 0,
						use_default_value : 0,
						change : ''
					},
					fieldDefaultValue : ''
				},
				currentField : {},
				indexEditForm : 0
			}
	    },
		methods: {
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
					fieldOperation : {
						ignore : 0,
						use_default_value : 0,
						change : ''
					},
					fieldDefaultValue : ''
				};
			}
		}
	}
</script>