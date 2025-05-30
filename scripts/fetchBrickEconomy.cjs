const { execSync } = require("child_process");

(async () => {
    const urls = {
        '1234': "sjabdioabvlaj/asgaga/sasddasdasdasdb.jpg",
        '2234': "sjabdioabvlaj/asgaga/sasddasdasdasdb.jpg",
        '3234': "sjabdioabvlaj/asgaga/sasddasdasdasdb.jpg",
        '4234': "sjabdioabvlaj/asgaga/sasddasdasdasdb.jpg",
    };

    try {
        throw new Error("This is a test error for logging!");
    } catch (error) {
        execSync(
            `php artisan log:error ${0} "Some error occured" 500 "${Buffer.from(
                JSON.stringify({ error: error.toString(), urls: urls })
            ).toString("base64")}"`
        );
    }
})();
