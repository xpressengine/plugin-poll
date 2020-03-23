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
                elementDoubleClick: function () {},
                beforeSubmit: function (editor) {},
                editorLoaded: function (editor) {}
            }
        })
    })
})(window.jQuery, window.XE)
