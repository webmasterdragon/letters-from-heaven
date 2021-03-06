download.addEventListener("click", function() {
    // only jpeg is supported by jsPDF
    var imgData = canvas.toDataURL("image/jpeg", 1.0);
    var pdf = new jsPDF();

    pdf.addImage(imgData, 'JPEG', 0, 0);
    pdf.save("download.pdf");
}, false);

function doexportjson() {

    fabric.log('JSON without additional properties: ', canvas.toJSON());
    var saving_data = canvas.toJSON();
    alert("You clicked save button");
}
function dohelp() {

    alert("You clicked Help button");
}

var stack = new Undo.Stack(),
    EditCommand = Undo.Command.extend({
        constructor: function(textarea, oldValue, newValue) {
            this.textarea = textarea;
            this.oldValue = oldValue;
            this.newValue = newValue;
        },
        execute: function() {
        },
        undo: function() {
            this.textarea.html(this.oldValue);
        },

        redo: function() {
            this.textarea.html(this.newValue);
        }
    });
stack.changed = function() {
    stackUI();
};

var undo = $(".undo"),
    redo = $(".redo"),
    dirty = $(".dirty");
function stackUI() {
    undo.attr("disabled", !stack.canUndo());
    redo.attr("disabled", !stack.canRedo());
    dirty.toggle(stack.dirty());
}
stackUI();

$(document.body).delegate(".undo, .redo, .save", "click", function() {
    var what = $(this).attr("class");
    stack[what]();
    return false;
});

var text = $("#text"),
    startValue = text.html(),
    timer;
$("#text").bind("keyup", function() {
    // a way too simple algorithm in place of single-character undo
    clearTimeout(timer);
    timer = setTimeout(function() {
        var newValue = text.html();
        // ignore meta key presses
        if (newValue != startValue) {
            // this could try and make a diff instead of storing snapshots
            stack.execute(new EditCommand(text, startValue, newValue));
            startValue = newValue;
        }
    }, 250);
});

$(".bold").click(function() {
    document.execCommand("bold", false);
    var newValue = text.html();
    stack.execute(new EditCommand(text, startValue, newValue));
    startValue = newValue;
});

$(document).keydown(function(event) {
    if (!event.metaKey || event.keyCode != 90) {
        return;
    }
    event.preventDefault();
    if (event.shiftKey) {
        stack.canRedo() && stack.redo()
    } else {
        stack.canUndo() && stack.undo();
    }
});