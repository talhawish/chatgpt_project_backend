<?php

namespace App\Filament\Resources;

use Closure;
use Carbon\Carbon;
use Filament\Forms;
use App\Models\Post;
use Filament\Tables;
use App\Jobs\DeletePost;
use App\Models\Wordpress;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use App\Jobs\GeneratePostContent;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Model;

use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Controllers\WordpressController;
use App\Filament\Resources\PostResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\ImportAndGenerate;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationGroup = "Wordpress";
    protected static ?int $navigationSort = 2;

    protected static function getNavigationBadge(): ?string
    {
        return (string) Post::query()->count();
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([






                Section::make('Post')->schema([

                    Forms\Components\Select::make('website_id')
                        ->label('Website')
                        ->options(Wordpress::all()->pluck('website_url', 'id'))
                        // ->searchable()
                        ->reactive()

                        ->required(),




                    // ->hidden(function (callable $get) {
                    //     return empty($get('website'));
                    // })



                    Forms\Components\TextInput::make('title')
                        ->required()


                        ->maxLength(255),
                    Forms\Components\RichEditor::make('content')
                        ->required(),


                    Forms\Components\Select::make('categories')
                        ->required()
                        ->options(function (callable $get) {

                            $data = Wordpress::find($get('website_id'));
                            if ($data) {
                                $wp = new WordpressController($data['website_url'], $data['username'], $data['password']);
                                $result = (collect($wp->get_categories()))->pluck('name', 'id');
                                return $result;
                            }

                            return [1 => 'Uncategorised'];
                        })
                        ->multiple(),

                    Forms\Components\Select::make('status')
                        ->options([
                            'publish'  => 'publish',
                            'future' => 'future',
                            'draft' => 'draft',
                            'pending' => 'pending',
                            'private' => 'private',
                        ])
                        ->default('publish')
                        ->required(),

                    Forms\Components\DateTimePicker::make('scheduled_at'),
                    Forms\Components\Checkbox::make('published_status')->disabled(),
                    Forms\Components\Checkbox::make('needs_update')->disabled(),
                ]),

                Section::make('Tags')->schema([
                    Forms\Components\Select::make('tags')

                        ->options(function (callable $get) {

                            $data = Wordpress::find($get('website_id'));
                            if ($data) {
                                $wp = new WordpressController($data['website_url'], $data['username'], $data['password']);
                                $result = (collect($wp->get_tags()))->pluck('name', 'id');
                                return $result;
                            }

                            return [];
                        })
                        ->multiple()
                        ->reactive(),

                ]),

                Section::make('SEO')->schema([

                    Forms\Components\TextInput::make('meta_title')
                        ->disabled(),

                    Forms\Components\Textarea::make('meta_description')
                        ->disabled(),
                ]),
                Section::make('Post Details')->schema([
                    Forms\Components\TextInput::make('author')
                        ->disabled()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('slug')
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('date')->disabled(),
                    Forms\Components\DatePicker::make('date_gmt')->disabled(),
                    Forms\Components\TextInput::make('guid')
                        ->disabled()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('type')
                        ->disabled()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('link')
                        ->disabled()
                        ->maxLength(255),

                    Grid::make()->schema([
                        Forms\Components\Textarea::make('excerpt'),
                        // Forms\Components\Textarea::make('meta')
                        // ->maxLength(65535),
                    ])->columns(2),


                    Forms\Components\select::make('comment_status')
                        ->options([
                            'open' => 'open',
                            'closed' => 'closed',
                        ])->default('open'),

                    Forms\Components\select::make('ping_status')
                        ->options([
                            'open' => 'open',
                            'closed' => 'closed',
                        ])->default('open'),


                ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('website.website_url')->limit(20)->tooltip(fn ($record) => $record->website?->website_url)->sortable()->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->view('filament.tables.columns.text')
                    ->limit(20)->tooltip(fn ($record) => $record->title)->sortable()->searchable(),
                Tables\Columns\TextColumn::make('content')
                    ->view('filament.tables.columns.text')
                    ->getStateUsing(function ($record) {
                        return strip_tags($record->content);
                    })
                    ->limit(20)->tooltip(fn ($record) => $record->content)->sortable()->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->date(),

                Tables\Columns\BadgeColumn::make('scheduled_at')
                    ->getStateUsing(function ($record) {

                        if (!empty($record->scheduled_at) && $record->scheduled_at > Carbon::now() && !$record->published_status) {
                            return (new Carbon($record->scheduled_at))->format('Y-m-d - H:i');
                        }
                        return "";
                    })
                    ->sortable()->searchable()->searchable()

                    ->colors([
                        'warning',

                    ])
                    ->icons([
                        'heroicon-o-pause',

                    ]),

                Tables\Columns\BadgeColumn::make('status')->sortable()->searchable()->searchable()

                    ->colors([
                        'success',
                        'secondary' => 'future',
                        'warning' => 'draft',
                        'primary' => 'pending',
                        'danger' => 'private',
                    ])
                    ->icons([
                        'heroicon-o-check',
                        'heroicon-o-pause' => 'pending',
                        'heroicon-o-document' => 'future',
                        'heroicon-o-refresh' => 'draft',
                        'heroicon-o-lock-closed' => 'private',
                    ]),
                // Tables\Columns\TextColumn::make('wp_post_id'),
                // Tables\Columns\TextColumn::make('author'),
                // Tables\Columns\TextColumn::make('slug'),
                // Tables\Columns\TextColumn::make('date_gmt')
                //     ->date(),
                // Tables\Columns\TextColumn::make('guid'),
                // Tables\Columns\TextColumn::make('type'),
                // Tables\Columns\TextColumn::make('link'),
                // Tables\Columns\TextColumn::make('content'),
                // Tables\Columns\TextColumn::make('excerpt'),
                // Tables\Columns\TextColumn::make('comment_status'),
                // Tables\Columns\TextColumn::make('ping_status'),
                // Tables\Columns\TextColumn::make('meta'),
                // Tables\Columns\TagsColumn::make('categories'),
                // Tables\Columns\TextColumn::make('tags'),
                // Tables\Columns\TextColumn::make('meta_title'),
                // Tables\Columns\TextColumn::make('meta_description'),
                // Tables\Columns\TextColumn::make('created_at')
                //     ->dateTime(),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime(),
            ])
            ->defaultSort('created_at', "desc")
            ->filters([
                //
            ])
            ->poll('20s')
            ->actions([

                Tables\Actions\Action::make('Open')->button()->url(function ($record) {
                    return $record->link;
                }, true)
                    ->visible(function ($record) {
                        return $record->published_status;
                    })
                    ->color('secondary'),
                Tables\Actions\Action::make('Publich')->action(function ($record) {
                    $data = Wordpress::find($record->website_id);
                    $wp = new WordpressController($data['website_url'], $data['username'], $data['password']);

                    $wp->upload_post($record);
                })
                    ->button()
                    ->visible(function ($record) {
                        return !$record->published_status;
                    })
                    ->color('primary'),


                Tables\Actions\Action::make('Update')->action(function ($record) {

                    $data = Wordpress::find($record->website_id);
                    $wp = new WordpressController($data['website_url'], $data['username'], $data['password']);

                    $wp->edit_post($record);

                    Notification::make()
                        ->title('Updated')
                        ->body($record->title)
                        ->success()
                        ->send();
                })->button()
                    ->visible(function ($record) {
                        return $record->published_status;
                    })
                    ->color('secondary'),

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()->action(function ($records) {

                    $seconds = 3;

                    $records->each(
                        function (Model $record, $seconds) {


                            if (!empty($record->wp_post_id)) {
                                $isGenerateId = $record->importAndGenerate?->id;

                                if (ImportAndGenerate::find($isGenerateId)) {
                                    ImportAndGenerate::whereId($isGenerateId)->update(['is_generated' => false]);
                                }





                                $wpPostId = $record->wp_post_id;

                                $wordpress =  [
                                    'website_url' => $record->website?->website_url,
                                    'username' => $record->website?->username,
                                    'password' => $record->website?->password,
                                ];



                                $delete = (new DeletePost($wpPostId, $wordpress))->delay(Carbon::now()->addSeconds($seconds));
                                dispatch($delete);

                                $seconds += 3;
                            }
                            $record->delete();
                        }
                    );
                }),
                // Tables\Actions\BulkAction::make('Generate Content')->action(function ($records) {

                //     $seconds = 10;

                //     foreach($records as $record) {

                //         $title = $record->title;
                //         $id = $record->id;

                //         // $content = (new GeneratePostContent($title, $id))->delay(Carbon::now()->addSeconds($seconds));

                //         // dispatch($content);

                //         $seconds += 10;
                //     }

                //     Notification::make()
                //         ->title('Generate Content is processing ...')
                //         ->body($records->count() . ' Job is running in the background')
                //         ->warning()
                //         ->send();

                //     return true;


                // })
                // ->icon('heroicon-o-document')
                // ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}