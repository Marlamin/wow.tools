async function getDB2s(){
    const db2Response = await fetch("https://api.wow.tools/databases/");
    const db2Json = await db2Response.json();
    return db2Json;
}

async function initializeCommandPal(){
    const db2Response = await getDB2s();
    for(const db2 of db2Response)
    {
        commandPal.options.commands[0].children.push(
            {
                name: db2.displayName,
                handler: () => openDB2(db2.name)
            }
        );
    }

    commandPalInitialized = true;
}

function openDB2(db2){
    window.location = "/dbc/?dbc=" + db2;
}

const commandPal = new CommandPal({
    hotkey: "ctrl+shift+p",
    commands: [
        {
            name: "Go to DB2/DBC table",
            children: []
        },
        {
            name: "Toggle site theme",
            handler: () => toggleTheme()
        },
        {
            name: "Go to files",
            handler: () => {
                window.location = "/files/";
            }
        },
        {
            name: "Go to model viewer",
            handler: () => {
                window.location = "/mv/";
            }
        },
        {
            name: "Go to monitor",
            handler: () => {
                window.location = "/monitor/";
            }
        },
    ],
}
);
let commandPalInitialized = false;

$(function() {
    commandPal.subscribe("closed", (e) => { 
        if(commandPalInitialized)
            return;

        initializeCommandPal();
    });

    commandPal.subscribe("opened", (e) => { 
        if(commandPalInitialized)
            return;

        initializeCommandPal();
    });

    commandPal.start();
});