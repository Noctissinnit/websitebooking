/** 
 * @param {FormData} form
 * @param {string[]} fields
*/
function validateEmptyForm(form, fields) {
    for (const key in fields) {
        if (form.has(key) && form.get(key).length === 0) {
            alert({
                title: "Error",
                text: `Kolom "${fields[key]}" harus di-isi!`,
                icon: "error"
            });
            return false;
        } else {
            console.error(`Missing key "${key}" on form empty validation. Ignoring...`);
        }
    }
    return true;
}