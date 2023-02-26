<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Models\Post;
use App\Jobs\DeletePost;
use App\Models\Wordpress;
use Filament\Pages\Actions;
use App\Filament\Resources\PostResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Http\Controllers\ChatgptController;
use App\Http\Controllers\WordpressController;
// use Barryvdh\DomPDF\Facade\Pdf;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getActions(): array
    {
        return [

            Actions\DeleteAction::make()->action(function () {
                if (!empty($this->record->wp_post_id)) {

                    // $wp = new WordpressController($this->record->website->website_url, $this->record->website->username, $this->record->website->password);
    
                    // $wp->deletePost($this->record->wp_post_id);
    
                    if (!empty($this->record->wp_post_id)) {
                        DeletePost::dispatch($this->record);
                    } else {
                        $this->record->delete();
                    }


                }
                // dd($this->record->id);
                // Post::whereId($this->record->id)->delete();
                $this->record->delete();
                redirect('posts');


            }),

            Actions\Action::make('Open post')->button()->url($this->record->link, true)->visible(function () {
                return $this->record->published_status;
            })->color('secondary'),


            // Actions\Action::make('Save PDF')->button()->action(function () {
            //     Pdf::loadHTML($this->record->title . "</br>" . $this->record->content)->setPaper('a4', 'portrait')->setWarnings(false)->save($this->record->title . '.pdf');
            // })->color('primary'),


           


            // Actions\Action::make('Re-Generate Content')->action(function () {

            //     dd($this->record->ImportAndGenerate());
                
            //     $gpt = new ChatgptController();

            //     $this->data['content'] = $gpt->generatePostContent($this->record->title);

            //     $this->save(false);


            // })->color('warning'),



            Actions\Action::make('Publish')->action(function () {
                $data = Wordpress::find($this->record->website_id);
                $wp = new WordpressController($data['website_url'], $data['username'], $data['password']);
                $wp->upload_post($this->record);
            })
            ->visible(function () {
                return !$this->record->published_status; })
            ->color('success'),


            Actions\Action::make('Update')->action(function () {
                $data = Wordpress::find($this->record->website_id);
                $wp = new WordpressController($data['website_url'], $data['username'], $data['password']);

                // dd($wp->get_post($this->record->wp_post_id));

                $wp->edit_post($this->record);

                Notification::make()
                    ->title('Updated')
                    ->body($this->record->title)
                    ->success()
                    ->send();
            })
                ->visible(function () {
                    return $this->record->published_status; })
            ->color('secondary'),

        ];
    }
}
