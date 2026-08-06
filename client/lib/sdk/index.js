/**
 * SharaForms SDK Module
 * Internal SDK utilities for form-parent communication
 */

export { useSdkBridge, createLocalSDK, SharaFormsLocalSDK, EVENTS } from './useSdkBridge'
export {
  EDITOR_EVENTS,
  EDITOR_MSG_TYPE,
  emitEditorEvent,
  emitEditorFormDeleted,
  emitEditorFormSaved,
  emitEditorNavigateBack,
  resolveEditorRouteView,
  resolveEditorView,
} from './editorEmbedBridge'

