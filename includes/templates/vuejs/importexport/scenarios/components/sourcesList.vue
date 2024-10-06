<template>
	<div>
		<div id="sourcesAdd" class='row ie-row'>
			<div class="colonne25">
				<label class='etiquette' for='sourceAddName'>{{ messages.get('importexport', 'ie_source') }} :</label>
			</div>
			<div class="colonne75">
				<input type='text' id='sourceAddName' name='sourceAddName' class='saisie-50em' :placeholder='messages.get("importexport", "ie_source_name")' v-model='source.sourceName' />
				<label class='visually-hidden' for='sourcesTypes'>{{ messages.get('importexport', 'ie_sources_types') }}</label>
				<select id='sourcesTypes' name='sourcesTypes' v-model='source.sourceType'>
					<option value='' disabled>{{ messages.get('importexport', 'ie_sources_types_empty_value') }}</option>
					<option v-for='(sourceType, index) in sourcesTypes' :value='sourceType.namespace' :key="index">{{ sourceType.msg.name }}</option>
				</select>
				<button type='button' class='bouton' @click='addSource' :disabled='source.sourceName == "" || source.sourceType == ""'>
					<i class="fa fa-plus" aria-hidden="true"></i>
				</button>
			</div>
		</div>
		<div id="sourcesList">
			<table>
				<caption>{{ messages.get('importexport', 'ie_sources_list') }}</caption>
				<thead>
					<tr>
						<th>
							{{ messages.get('importexport', 'ie_source_name') }}
						</th>
						<th>
							{{ messages.get('importexport', 'ie_source_type') }}
						</th>
						<th>
							{{ messages.get('importexport', 'ie_actions') }}
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(source, index) in sources" :key="index" :class="source.id ? 'scenarioSourceEdit' : 'scenarioSourceAdd'">
						<td>{{ source.sourceName }}</td>
						<td>{{ getSourceTypeLabel(source.sourceType) }}</td>
						<td>
							<div v-if="source.id">
								<button type="button" class="bouton" @click="edit(source)">{{ messages.get('common', 'edit') }}</button>
								<button type="button" class="bouton" @click="duplicate(source.id)">{{ messages.get('common', 'common_duplicate') }}</button>
								<button type="button" class="bouton" @click="remove(source.id)">{{ messages.get('common', 'remove') }}</button>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<form-modal
			class="ie-modal"
			ref="modal"
			:title="messages.get('importexport', 'ie_sources_modal_title_edit_source')"
			:modal-style="{width: '75%'}"
			:show-save="true"
			:show-duplicate="true"
			:show-delete="true"
			@submit="save(currentSource)"
			@duplicate="duplicate(currentSource.id)"
			@remove="remove(currentSource.id)">
			<sources-edit-form
				v-if="currentSourceType && currentSource"
				:source="currentSource"
				:currentSourceType="currentSourceType"
				:key="indexEditForm"></sources-edit-form>
		</form-modal>

	</div>
</template>

<script>
	import FormModal from '@/common/components/FormModal.vue';
	import sourcesEditForm from '@importexport/sources/sourcesEditForm.vue';

	export default {
		components : {
			FormModal,
			sourcesEditForm
		},
		props: {
			sources : {
				'type' : Array
			},
			sourcesTypes : {
				'type' : Array
			},
			idScenario : {
				'type' : Number
			}
		},
		created: function() {
			this.source = this.getEmptySource();
		},
		data: function() {
			return {
				source : {},
				currentSource : {},
				indexEditForm : 0
			}
	    },
		methods: {
			edit: function (source) {
				this.currentSource = source;
				//Permet de faire le rerender de la modal avec les nouvelles donnees
				this.indexEditForm++;
				this.$refs.modal.show();
		    },
		    save : async function(source) {
				let response = await this.ws.post('sources', 'save', source);
				if(!response.error) {
					this.notif.info(this.messages.get('common', 'success_save'));
					this.$refs.modal.close();
					return response;
				}
				return false;
			},
			duplicate : async function(id) {
		    	if(confirm(this.messages.get('importexport', 'ie_source_duplicate_confirm'))) {
		    		let source = await this.ws.post('sources', 'duplicate', { id : id });
		    		if(!source.error) {
		    			this.sources.push(source);
						this.$refs.modal.close();
		    		} else {
		    			this.notif.error(this.messages.get('common', 'failed_delete'));
		    		}
		    	}
			},
			remove: async function (id) {
				if(confirm(this.messages.get('importexport', 'ie_source_remove_confirm'))) {
					let remove = await this.ws.post('sources', 'remove', { id : id });
					if(!remove.error) {
						this.notif.info(this.messages.get('common', 'success_delete'));
						let i = this.sources.findIndex(i => i.id == id);
						if(i != -1) {
							this.sources.splice(i, 1);
						}
						this.$refs.modal.close();
					} else {
						this.notif.error(remove.errorMessage);
					}
				}
		    },
		    addSource: async function() {
	        	if(this.idScenario) {
	        		let response = await this.save(this.source);
	        		if(response) {
	        			this.sources.push(response);
	        		}
	        	} else {
					const source = Object.assign({}, this.source);
	        		this.sources.push(source);
	        	}
				this.source = this.getEmptySource();
	        },
	        getSourceTypeLabel : function(sourceType) {
				let source = this.sourcesTypes.find( source => source.namespace == sourceType);
				if(source && source.msg) {
					return source.msg.name;
				}
				return "";
			},
			getEmptySource: function() {
				let idScenario = this.idScenario ? this.idScenario : 0;
				return {
					sourceName : '',
					sourceType : '',
					numScenario : idScenario
				};
			}
		},
		computed: {
			currentSourceType : function() {
				return this.sourcesTypes.find(el => el.namespace == this.currentSource.sourceType )
			}
		}
	}
</script>