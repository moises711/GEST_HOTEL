{ pkgs, ... }: {
  channel = "stable-23.11";
  packages = [
    pkgs.nodejs_20,
    pkgs.python3,
    pkgs.php,
    pkgs.composer
  ];
  idx = {
    extensions = [
      "vscodevim.vim"
    ];
    workspace = {
      onCreate = {
        "npm-install" = "npm install";
        "composer-install" = "composer install";
      };
    };
    previews = {
      enable = true;
      previews = {
        web = {
          command = ["npm" "run" "dev" "--" "--port" "$PORT" "--host" "0.0.0.0"];
          manager = "web";
        };
      };
    };
  };
}
