const fs = require('fs');
const path = require('path');
const { parse } = require('@vue/compiler-sfc');

const files = [
  'components/open/forms/components/FormEditorNavbar.vue',
  'components/open/forms/components/FormFieldsEditor.vue',
  'components/open/forms/components/form-components/FormEditorPreview.vue',
  'components/open/forms/components/form-components/AddFormBlock.vue',
  'components/open/forms/fields/FormFieldEdit.vue',
  'components/open/forms/OpenFormField.vue',
  'components/open/editors/UndoRedo.vue',
  'components/open/editors/EditorOptionsPanel.vue',
  'components/open/editors/EditorRightSidebar.vue',
  'components/open/editors/FormHistory.vue',
  'components/open/forms/FormProgressbar.vue',
  'components/open/forms/components/BlockTypeIcon.vue',
  'components/open/forms/components/CopyContent.vue',
  'components/open/forms/components/FormEditorSkeleton.vue',
  'components/open/forms/components/FormStats.vue',
  'components/open/forms/components/FormStatusBadges.vue',
  'components/open/forms/components/SeoPreview.vue',
  'components/open/forms/components/AdvancedFormUrlSettings.vue',
  'components/open/forms/components/FormSummary.vue',
  'components/open/forms/components/FormEditor.vue',
  'components/open/forms/components/form-components/EditorSectionHeader.vue',
  'components/open/forms/components/form-components/FormCustomization.vue',
];

let hasError = false;
files.forEach(f => {
  const src = fs.readFileSync(f, 'utf8');
  const { errors } = parse(src, { filename: f });
  if (errors.length) {
    hasError = true;
    console.log('ERROR in ' + f + ':');
    errors.forEach(e => console.log('  ' + e.message));
  } else {
    console.log('OK: ' + f);
  }
});
if (!hasError) console.log('\nAll templates compile successfully!');
else process.exit(1);
