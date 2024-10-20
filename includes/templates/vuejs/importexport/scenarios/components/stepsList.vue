<template>
	<div>
		<div id="stepsAdd" class='row ie-row'>
			<div class="colonne25">
				<label class='etiquette' for='stepAddName'>{{ messages.get('importexport', 'ie_step') }} :</label>
			</div>
			<div class="colonne75">
				<input type='text' id='stepAddName' name='stepAddName' class='saisie-50em' :placeholder='messages.get("importexport", "ie_step_name")' v-model='step.stepName' />
				<label class='visually-hidden' for='stepsTypes'>{{ messages.get('importexport', 'ie_steps_types') }}</label>
				<select id='stepsTypes' name='stepsTypes' v-model='step.stepType'>
					<option value='' disabled>{{ messages.get('importexport', 'ie_steps_types_empty_value') }}</option>
					<option v-for='(stepType, index) in stepsTypes' :value='stepType.value' :key="index">{{ stepType.msg[stepType.value] }}</option>
				</select>
				<button type='button' class='bouton' @click='addStep' :disabled='step.stepName == "" || step.stepType == ""'>
					<i class="fa fa-plus" aria-hidden="true"></i>
				</button>
			</div>
		</div>
		<div id="stepsList">
			<table>
				<caption>{{ messages.get('importexport', 'ie_steps_list') }}</caption>
				<thead>
					<tr>
						<th>
						</th>
						<th>
							{{ messages.get('importexport', 'ie_step_name') }}
						</th>
						<th>
							{{ messages.get('importexport', 'ie_step_type') }}
						</th>
						<th>
							{{ messages.get('importexport', 'ie_actions') }}
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(step, index) in sortedSteps" :key="index" :class="step.id ? 'scenarioStepEdit' : 'scenarioStepAdd'">
						<td>
							<button v-if="index > 0" type="button" class="bouton"
		                        @click="moveUp(index)" :title=" messages.get('importexport', 'ie_move_up')">
		                        <i class="fa fa-arrow-up" aria-hidden="true"></i>
		                    </button>
		                    <button v-if="index < steps.length - 1" type="button" class="bouton"
		                        @click="moveDown(index)" :title=" messages.get('importexport', 'ie_move_down')">
		                        <i class="fa fa-arrow-down" aria-hidden="true"></i>
		                    </button>
						</td>
						<td>{{ step.stepName }}</td>
						<td>{{ getStepTypeLabel(step.stepType) }}</td>
						<td>
							<div v-if="step.id">
								<button type="button" class="bouton" @click="edit(step)">{{ messages.get('common', 'edit') }}</button>
								<button type="button" class="bouton" @click="duplicate(step.id)">{{ messages.get('common', 'common_duplicate') }}</button>
								<button type="button" class="bouton" @click="remove(step.id)">{{ messages.get('common', 'remove') }}</button>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<form-modal
			class="ie-modal"
			ref="modal"
			:title="messages.get('importexport', 'ie_steps_modal_title_edit_step')"
			:modal-style="{width: '75%'}"
			:show-save="true"
			:show-duplicate="true"
			:show-delete="true"
			@submit="save(currentStep)"
			@duplicate="duplicate(currentStep.id)"
			@remove="remove(currentStep.id)">
			<steps-edit-form
				v-if="currentStepType && currentStep"
				:step="currentStep"
				:currentStepType="currentStepType"
				:sources="sources"
				:key="indexEditForm"></steps-edit-form>
		</form-modal>
	</div>
</template>

<script>
	import FormModal from '@/common/components/FormModal.vue';
	import stepsEditForm from '@importexport/steps/stepsEditForm.vue';

	export default {
		components : {
			FormModal,
			stepsEditForm
		},
		props: {
			steps : {
				'type' : Array
			},
			stepsTypes : {
				'type' : Array
			},
			idScenario : {
				'type' : Number
			},
			sources : {
				'type' : Array
			}
		},
		created: function() {
			this.step.numScenario = this.idScenario;
		},
		computed: {
			sortedSteps: function() {
				return this.steps.sort((a,b) => a.stepOrder > b.stepOrder);
			},
			currentStepType : function() {
				return this.stepsTypes.find(el => el.value == this.currentStep.stepType )
			}
		},
		data: function() {
			return {
				step : {
					stepName : '',
					stepType : '',
					numScenario : 0,
					source : {
						id : 0
					}
				},
				currentStep : {},
				indexEditForm : 0
			}
	    },
		methods: {
			edit: function (step) {
				this.currentStep = step;
				//Permet de faire le rerender de la modal avec les nouvelles donnees
				this.indexEditForm++;
				this.$refs.modal.show();
		    },
		    save : async function(step) {
				let response = await this.ws.post('steps', 'save', step);
				if(!response.error) {
					this.notif.info(this.messages.get('common', 'success_save'));
					this.$refs.modal.close();
					return response;
				}
				return false;
			},
		    duplicate : async function(id) {
		    	if(confirm(this.messages.get('importexport', 'ie_step_duplicate_confirm'))) {
		    		let step = await this.ws.post('steps', 'duplicate', { id : id, stepOrder : this.steps.length });
		    		if(!step.error) {
		    			this.steps.push(step);
						this.$refs.modal.close();
		    		} else {
		    			this.notif.error(this.messages.get('common', 'failed_delete'));
		    		}
		    	}
			},
			remove: async function (id) {
				if(confirm(this.messages.get('importexport', 'ie_step_remove_confirm'))) {
					let remove = await this.ws.post('steps', 'remove', { id : id });
					if(!remove.error) {
						this.notif.info(this.messages.get('common', 'success_delete'));
						let i = this.steps.findIndex(i => i.id == id);
						if(i != -1) {
							this.steps.splice(i, 1);
						}
						this.$refs.modal.close();
					} else {
						this.notif.error(this.messages.get('common', 'failed_delete'));
					}
				}
		    },
		    moveUp: function(i) {
	            if(i > 0 && this.steps[i].stepOrder) {
                	this.steps[i].stepOrder--;
                	this.steps[i-1].stepOrder++;
	                this.saveStepsOrder();
	            }
	        },
	        moveDown: function(i) {
	            if(i < this.steps.length-1) {
                	this.steps[i].stepOrder++;
                	this.steps[i+1].stepOrder--;
	            	this.saveStepsOrder();
	            }
	        },
	        saveStepsOrder: async function() {
	        	if(this.idScenario) {
	        		let response = await this.ws.post('scenarios', 'saveStepsOrder/'+this.idScenario, {'steps' : this.steps});
	        		if(!response.error) {
	        			this.notif.info(this.messages.get('common', 'success_save'));
	        		}
	        	}
	        },
	        addStep: async function() {
	        	this.step.stepOrder = this.steps.length;
	        	if(this.idScenario) {
	        		let response = await this.ws.post('steps', 'save', this.step);
	        		if(!response.error) {
	        			this.notif.info(this.messages.get('common', 'success_save'));
	        			this.steps.push(response);
	        		}
	        	} else {
					const step = Object.assign({}, this.step);
	        		this.steps.push(step);
	        	}
				this.step = this.getEmptyStep();
	        },
	        getStepTypeLabel: function(stepType) {
	        	let step = this.stepsTypes.find(element => element.value == stepType);
	        	if(step && step.msg) {
					return step.msg[stepType];
				}
	        	return "";
	        },
	        getEmptyStep: function() {
				return {
					stepName : '',
					stepType : '',
					numScenario : 0
				};
			}
		}
	}
</script>