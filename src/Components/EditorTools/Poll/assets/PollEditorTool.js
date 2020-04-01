// @ES5
// @FIXME window.XePollToolURL
(function ($, XE) {
    var windowName = 'editortoolXePoll'

    XE.app('Editor', function pollEditorTool (Editor) {
        Editor.defineTool({
            id: 'editortool/poll_tool@poll',
            props: {
                name: 'PollEditorTool',
                options: {
                    label: 'Poll',
                    command: 'pollCreate'
                },
                addEvent: {
                    doubleClick: false,
                }
            },
            css: function () {},
            events: {
                iconClick: function (editor, cbAppendToolContent) {
                    var targetEditor = editor.props.editor
                    // XE.Utils.openWindow(window.iframeToolURL.get('popup'), windowName, windowFeatures)
                    window.open(window.XePollToolURL.get('popup'), windowName, 'width=700,height=500')

                    Editor.$$once('editorTools.XePollTool.popup', function (eventName, obj) {
                        obj.init(targetEditor, cbAppendToolContent);
                    })
                },
                elementDoubleClick: function () {
                  var $editor = $(this).closest('.cke')
                  var editorId = String($editor.attr('id')).replace('cke_', '') || null
                  var pollId = $(this).data('id') || null

                  if (!editorId) {
                    return
                  }

                  var editor = window.CKEDITOR.instances[editorId]
                  console.debug('editor', editor)

                  var targetEditor = editor
                    window.open(window.XePollToolURL.get('popup'), windowName, 'width=700,height=500')

                    Editor.$$once('editorTools.XePollTool.popup', function (eventName, obj) {
                        obj.edit(targetEditor, { pollId: pollId })
                    })
                },
                beforeSubmit: function (editor) {
                  // console.debug('beforeSubmit', editor)
                },
                editorLoaded: function (editor) {}
            }
        })
    })
})(window.jQuery, window.XE)
