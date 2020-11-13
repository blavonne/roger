<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8" />
<title>blavonne 21</title>
<link rel="stylesheet" href="/style.css">
</head>
<body>
	<script>
		var i, j, elements = [];
		var n = 20;
		var map = document.createElement('div');
		var btn = document.createElement('input');
		
		map.className = 'map';
		btn.setAttribute("type", "button");
		btn.setAttribute("value", "Still alive");
		btn.setAttribute("onclick", "chacha()");
		document.body.appendChild(map);
		document.body.appendChild(btn);
		for (i = 0; i < n; i++)
		{
			elements[i] = [];
			for (j = 0; j < n; j++)
			{
				elements[i][j] = document.createElement('div');
				elements[i][j].className = 'dead';
				map.appendChild(elements[i][j]);
			}
		}
		for (i = 0; i < n; i++)
		{
			for (j = 0; j < n; j++)
			{
				elements[i][j].addEventListener('click', function(live)
				{
					live.target.classList.remove('dead');
					live.target.classList.add('alive');
				}, false);
			}
		}
		function next_gen()
		{
			var friends;

			for (i = 0; i < n; i++)
			{
				friends = 0;
				for (j = 0; j < n; j++)
				{
					if (elements[i][j].target.classList.contains('alive')
					{
						if (i - 1 >= 0 && j - 1 >= 0)
						{
							if (elements[i - 1][j - 1].target.classList.contains('alive'))
								friends++;
						}
						if (j - 1 >= 0)
						{
							if (elements[i][j - 1].target.classList.contains('alive'))
								friends++;
						}
						if (i + 1 < n && j - 1 >= 0)
						{
							if (elements[i + 1][j - 1].target.classList.contains('alive'))
								friends++;
						}
						if (i - 1 >= 0)
						{
							if (elements[i - 1][j].target.classList.contains('alive'))
								friends++;
						}
						if (i - 1 >= 0 && j + 1 < n)
						{
							if (elements[i - 1][j + 1].target.classList.contains('alive'))
								friends++;
						}
						if (j + 1 < n)
						{
							if (elements[i][j + 1].target.classList.contains('alive'))
								friends++;
						}
						if (i + 1 < n)
						{
							if (elements[i + 1][j].target.classList.contains('alive'))
								friends++;
						}
						if (i + 1 < n && j + 1 < n)
						{
							if (elements[i + 1][j + 1].target.classList.contains('alive'))
								friends++;
						}
						if (friends < 2 || friends > 3)
							elements[i][j].target.classList.add('can_die');
					}
					else if (elements[i][j].target.classList.contains('dead')
					{
						if (i - 1 >= 0 && j - 1 >= 0)
						{
							if (elements[i - 1][j - 1].target.classList.contains('alive'))
								friends++;
						}
						if (j - 1 >= 0)
						{
							if (elements[i][j - 1].target.classList.contains('alive'))
								friends++;
						}
						if (i + 1 < n && j - 1 >= 0)
						{
							if (elements[i + 1][j - 1].target.classList.contains('alive'))
								friends++;
						}
						if (i - 1 >= 0)
						{
							if (elements[i - 1][j].target.classList.contains('alive'))
								friends++;
						}
						if (i - 1 >= 0 && j + 1 < n)
						{
							if (elements[i - 1][j + 1].target.classList.contains('alive'))
								friends++;
						}
						if (j + 1 < n)
						{
							if (elements[i][j + 1].target.classList.contains('alive'))
								friends++;
						}
						if (i + 1 < n)
						{
							if (elements[i + 1][j].target.classList.contains('alive'))
								friends++;
						}
						if (i + 1 < n && j + 1 < n)
						{
							if (elements[i + 1][j + 1].target.classList.contains('alive'))
								friends++;
						}
						if (friends == 3)
							elements[i][j].target.classList.add('can_born');
					}
				}
			}
			for (i = 0; i < n; i++)
			{
				for (j = 0; j < n; j++)
				{
					if (elements[i][j].target.classList.contains('can_die'))
					{
						elements[i][j].target.classList.remove('can_die');
						elements[i][j].target.classList.remove('alive');
						elements[i][j].target.classList.add('dead');
					}
					else if (elements[i][j].target.classList.contains('can_born'))
					{
						elements[i][j].target.classList.remove('can_born');
						elements[i][j].target.classList.remove('dead');
						elements[i][j].target.classList.add('alive');
					}
				}
			}
		}
		function stay_alive()
		{
			for (i = 0; i < n; i++)
			{
				for (j = 0; j < n; j++)
				{
					if (elements[i][j].target.classList.contains('alive'))
						return true;
				}
			}
			return false;
		}
		function chacha()
		{
			while (stay_alive())
			{
				next_gen();
			}
		}
	</script>
</body>
</html>
