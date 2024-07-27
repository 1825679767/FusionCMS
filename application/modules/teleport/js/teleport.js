var goldRegex, dpRegex, vpRegex, freeRegex;

var Teleport = {

	User: {

		vp: null,
		dp: null,

		initialize: function(vp, dp)
		{
			var setLang = function()
			{
				if(typeof Language != "undefined")
				{
					goldRegex = new RegExp(lang("gold", "teleport"));
					dpRegex = new RegExp(lang("dp", "teleport"));
					vpRegex = new RegExp(lang("vp", "teleport"));
					freeRegex = new RegExp(lang("free", "teleport"));
				}
				else
				{
					setTimeout(setLang, 50);
				}
			};

			if(!goldRegex)
			{
				setLang();
			}

			this.vp = vp;
			this.dp = dp;
		}
	},

	Character: {

		name: null,
		guid: null,
		gold: null,

		initialize: function(name, guid, gold)
		{
			this.name = name;
			this.guid = guid;
			this.gold = gold;
		}
	},

	selectCharacter: function(button, realm, guid, name, gold, race)
	{
		Teleport.Character.initialize(name, guid, gold);

		const factions = {
			1: 1,
			3: 1,
			4: 1,
			7: 1,
			11: 1,
			22: 1,
			25: 1,
			29: 1,
			30: 1,
			32: 1,
			34: 1,
			37: 1,
			52: 1,
			2: 2,
			5: 2,
			6: 2,
			8: 2,
			9: 2,
			10: 2,
			26: 2,
			27: 2,
			28: 2,
			31: 2,
			35: 2,
			36: 2,
			70: 2,
			24: 3,
		};

		const faction = factions[race];

		$(".item_group").each(function()
		{
			$(this).removeClass("item_group").addClass("character-select");
			$(this).find(".nice_active").removeClass("nice_active").html("Select");
		});

		$(button).parents("tr").removeClass("character-select").addClass("item_group");
		$(button).addClass("nice_active").html(lang("selected", "teleport"));

		this.hideLocations(function()
		{
			Teleport.showLocations(realm, faction);
		});
	},

	hideLocations: function(callback)
	{
		$(".location").fadeOut(200);
		setTimeout(callback, 220);
	},
	
	showLocations: function(realm, factionId)
	{
		const field = $(".location-select[data-realm='" + realm + "']:first");

		const faction = field.attr("data-faction");

		if(faction == 0 || faction == factionId)
		{
			field.show(100, function()
			{
				Teleport.showLocation(this, realm, factionId);
			});
		}
		else
		{
			Teleport.showLocation(field, realm, factionId);
		}
	},

	showLocation: function(field, realm, faction)
	{
		try
		{
			const nextField = $(field).next(".location-select[data-realm='" + realm + "']");

			if(nextField.attr("data-faction") == 0 || nextField.attr("data-faction") == faction)
			{
				nextField.show(100, function()
				{
					Teleport.showLocation(this, realm, faction);
				});
			}
			else
			{
				Teleport.showLocation(nextField[0], realm, faction);
			}
		}
		catch(error)
		{
			// This was the last element
		}
	},

	buy: function(id, button)
	{
		let price = $(button).html().replace(/\<.*\/?\>/g, ""),
			canTeleport = false;

		if(freeRegex.test(price))
		{
			canTeleport = true;
		}
		else if(vpRegex.test(price))
		{
			price = price.replace(vpRegex, "");
			price = parseInt(price);

			if(Teleport.User.vp < price)
			{
				Swal.fire(lang("cant_afford", "teleport"), '', 'error');
			}
			else
			{
				canTeleport = true;
			}
		}
		else if(dpRegex.test(price))
		{
			price = price.replace(dpRegex, "");
			price = parseInt(price);

			if(Teleport.User.dp < price)
			{
				Swal.fire(lang("cant_afford", "teleport"), '', 'error');
			}
			else
			{
				canTeleport = true;
			}
		}
		else if(goldRegex.test(price))
		{
			price = price.replace(goldRegex, "");
			price = parseInt(price);

			if(Teleport.Character.gold < price)
			{
				Swal.fire(lang("cant_afford", "teleport"), '', 'error');
			}
			else
			{
				canTeleport = true;
			}
		}
		else
		{
			Swal.fire("Unknown price type", '', 'error');
		}

		if(canTeleport)
		{
			// Teleport
			$.post(Config.URL + "teleport/submit", {id:id, guid:Teleport.Character.guid, csrf_token_name: Config.CSRF}, function(data)
			{
				if(data == 1)
				{
					Swal.fire(Teleport.Character.name + " " + lang("teleported", "teleport"), '', 'success');
				}
				else
				{
					Swal.fire(data, '', 'warning');
				}
			});

			// Hide and pass an empty function to prevent undefined callback error
			Teleport.hideLocations(function()
			{
				$(".item_group").each(function()
				{
					$(this).removeClass("item_group").addClass("character-select");
					$(this).find(".nice_active").removeClass("nice_active").html(lang("select", "teleport"));
				});
			});
		}
	}
}
