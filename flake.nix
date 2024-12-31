{
  description = "development environment";

  inputs = { nixpkgs.url = "github:nixos/nixpkgs/nixos-24.11"; };

  outputs = { nixpkgs, ... }:
    let system = "x86_64-linux";
    in {
      devShells."${system}".default =
        let pkgs = import nixpkgs { inherit system; };
        in pkgs.mkShell {
          packages = with pkgs; [
            curl
            php82
            php82.packages.composer
            symfony-cli
            nodejs_20
            postgresql_15
          ];

          shellHook = ''
            source .env
          '';
        };
    };
}
