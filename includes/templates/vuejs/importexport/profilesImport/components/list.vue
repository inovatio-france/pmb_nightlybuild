<template>
	<div>
		<div id="list">
			<table>
				<thead>
					<tr>
						<th>
							{{ messages.get('importexport', 'ie_profile_name') }}
						</th>
						<th>
							{{ messages.get('importexport', 'ie_profile_comment') }}
						</th>
						<th>
							{{ messages.get('importexport', 'ie_actions') }}
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(profile, index) in list" :key="index">
						<td>{{ profile.profileName }}</td>
						<td>{{ profile.profileComment }}</td>
						<td>
							<button type="button" class="bouton" @click="edit(profile.idProfile)">{{ messages.get('common', 'edit') }}</button>
							<button type="button" class="bouton" @click="remove(profile.idProfile)">{{ messages.get('common', 'remove') }}</button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="add">
			<button type="button" class="bouton" @click="add()">{{ messages.get('importexport', 'ie_profile_add') }}</button>
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
		        document.location = './import_export.php?categ=profiles_import&action=edit';
		    },
			edit: function (id) {
		        document.location = './import_export.php?categ=profiles_import&action=edit&id='+id;
		    },
		    remove : function(id) {
		    	document.location = './import_export.php?categ=profiles_import&action=remove&id='+id;
			},
		}
	}
</script>